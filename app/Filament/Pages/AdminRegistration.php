<?php

namespace App\Filament\Pages; // Or App\Filament\Admin\Pages if you follow that structure

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService; // Your SMS Service
use Filament\Actions\Action;
use App\Notifications\NewAdminCreatedNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade; //using it with nickname

class AdminRegistration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament-panels::pages.auth.register';
    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Jisajili Msimamizi Mkuu';
    protected static ?string $slug = 'custom-admin-registration-page';

    public ?array $formData = [];

    public function mount(): void
    {
        if (auth()->guard(config('filament.auth.guard', 'web'))->check()) {
             redirect(config('filament.home_url', '/admin'));
        }
        $this->form->fill();
    }

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Jina Kamili la Msimamizi')
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Barua Pepe')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email')
                    ->helperText('Tumia barua pepe halali na ambayo haijatumika.'),
                TextInput::make('phone_no')
                    ->label('Namba ya Simu') // "Phone Number"
                    ->tel()
                    ->required()
                    // Regex validates: 0 followed by 9 digits OR +255 followed by optional space and 9 digits
                    ->regex('/^(0\d{9}|\+255\s?\d{9})$/')
                    // ->unique(User::class, 'phone_no') // Keep commented as per original, but uncommenting is recommended
                    ->helperText('Weka namba kwa muundo wa 07XXXXXXXX au +2557XXXXXXXX.')
                    ->validationMessages([
                        'regex' => 'Muundo wa namba ya simu si sahihi. Tafadhali fuata mfano uliowekwa.',
                    ]),
                TextInput::make('password')
                    ->label('Nenosiri Imara')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->confirmed()
                    ->helperText('Nenosiri liwe na angalau herufi 8 kwa usalama.'),
                TextInput::make('password_confirmation')
                    ->label('Thibitisha Nenosiri')
                    ->password()
                    ->required()
                    ->minLength(8),
            ])
            ->statePath('formData');
    }

    public function handleAdminRegistration(): void
    {
        $validatedData = $this->form->getState();

        try {
            // Normalize the phone number to the standard +255 format before saving
            $phone_no = $validatedData['phone_no'];
            $phone_no = str_replace(' ', '', $phone_no); // Remove spaces, e.g., from "+255 7..."
            if (str_starts_with($phone_no, '0')) {
                // Replace the leading '0' with '+255'
                $phone_no = '+255' . substr($phone_no, 1);
            }

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_no' => $phone_no, // Save the normalized phone number
                'password' => Hash::make($validatedData['password']),
            ]);

            $adminRole = Role::firstOrCreate(
                ['name' => 'admin'],
                ['guard_name' => config('filament.auth.guard', 'web')]
            );
            $user->assignRole($adminRole);

            Log::info("Msimamizi mpya (admin) amesajiliwa: {$user->name}, {$user->email}, Simu: {$user->phone_no}");

            $otherAdmins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                ->where('id', '!=', $user->id)
                ->get();

            if ($otherAdmins->isNotEmpty()) {
                NotificationFacade::send($otherAdmins, new NewAdminCreatedNotification($user));
            }

            try {
                $smsService = app(SmsService::class);
                $message = "Karibu sana Bwana/Bibi {$user->name} kwenye mfumo wa WakalaTel! Akaunti yako ya Usimamizi Mkuu imeundwa na ipo tayari kutumika. Furahia huduma!";
                if ($smsService->sendSms($user->phone_no, $message)) {
                    Log::info("SMS ya kukaribisha imetumwa kwa msimamizi {$user->name} namba {$user->phone_no}.");
                } else {
                    Log::warning("SMS ya kukaribisha kwa msimamizi {$user->name} IMEGOMA kutumwa kwa {$user->phone_no} (lakini mtumiaji ameundwa).");
                }
            } catch (\Exception $smsException) {
                Log::error("Hitilafu kwenye Huduma ya SMS kwa {$user->phone_no} (usajili wa msimamizi): " . $smsException->getMessage());
            }

            Notification::make()
                ->title('Usajili Umekamilika!')
                ->body("Hongera sana {$user->name}! Akaunti yako ya msimamizi imeundwa. Karibu!")
                ->success()
                ->send();

            redirect()->route('custom.admin.registration.success', ['adminName' => $user->name]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug('Usajili wa Msimamizi umeshindikana (Validation Errors): ', $e->errors());
        } catch (\Exception $e) {
            Log::error("Hitilafu ya Jumla Wakati wa Usajili wa Msimamizi: " . $e->getMessage(), ['exception_trace' => $e->getTraceAsString()]);
            Notification::make()
                ->title('Usajili Umeshindikana Vibaya')
                ->body('Samahani, kumetokea tatizo la kiufundi lisilotarajiwa. Tafadhali jaribu tena baadaye au wasiliana na wasimamizi wa mfumo.')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('KAMILISHA USAJILI')
                ->submit('handleAdminRegistration')
                ->color('danger')
                ->button(),
        ];
    }

    public function getCachedFormActions()
    {
        return $this->getFormActions();
    }

    public function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
