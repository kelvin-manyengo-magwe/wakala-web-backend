<?php

namespace App\Filament\Pages; // Adjust if your panel's pages are in e.g., App\Filament\Admin\Pages

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Your User model
use Spatie\Permission\Models\Role; // Spatie Role model
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;     // Your SMS Service
use App\Filament\Pages\RegistrationSuccessPage; // The confetti page we'll create
use Filament\Actions\Action;      // For defining form actions (buttons)

class AdminRegistration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null; // Not in main navigation menu
    protected static string $view = 'filament.pages.admin-registration'; // Points to our Blade view

    // This title appears in the browser tab or Filament's header for the page (if not using simple layout fully)
    protected static ?string $title = 'Jisajili Kama Msimamizi';

    // This ensures it DOES NOT appear in the sidebar navigation
    protected static bool $shouldRegisterNavigation = false;

    // This is used by Filament to construct the URL.
    // For /admin/register, since panel path is /admin, slug should be 'register'
    protected static ?string $slug = 'register';

    public ?array $formData = []; // This will hold our form's data

    public function mount(): void
    {
        // If a user is already logged in, redirect them from the registration page
        if (auth()->guard(config('filament.auth.guard'))->check()) {
            redirect(config('filament.home_url'));
        }
        $this->form->fill(); // Initialize the form (empty by default)
    }

    // Define the form structure
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Jina Kamili la Msimamizi')
                    ->required()->autofocus()->maxLength(255),
                TextInput::make('email')
                    ->label('Barua Pepe')
                    ->email()->required()->maxLength(255)->unique(User::class, 'email')
                    ->helperText('Tumia barua pepe halali na ambayo haijatumika.'),
                TextInput::make('phone_no')
                    ->label('Namba ya Simu (anza na +255)')
                    ->tel()->required()->unique(User::class, 'phone_no')
                    ->helperText('Mfano: +255712345678. SMS itatumwa hapa.'),
                TextInput::make('password')
                    ->label('Nenosiri Imara')
                    ->password()->required()->minLength(8)->confirmed()
                    ->helperText('Angalau herufi 8 kwa usalama zaidi.'),
                TextInput::make('password_confirmation')
                    ->label('Thibitisha Nenosiri')
                    ->password()->required()->minLength(8),
            ])
            ->statePath('formData'); // Binds form state to $this->formData
    }

    // This public method will be called when the form's primary action is triggered
    public function submitRegistrationForm(): void
    {
        $validatedData = $this->form->getState();

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_no' => $validatedData['phone_no'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $adminRole = Role::firstOrCreate(
                ['name' => 'admin', 'guard_name' => config('filament.auth.guard', 'web')]
            );
            $user->assignRole($adminRole);

            Log::info("Msimamizi mpya (admin) ameundwa: {$user->email}, Simu: {$user->phone_no}");

            // Send Welcome SMS
            try {
                $smsService = app(SmsService::class); // Resolve SmsService from container
                $appName = config('app.name', 'WakalaTel');
                $message = "Karibu {$user->name} kwenye {$appName}! Akaunti yako ya Usimamizi Mkuu imefunguliwa. Unaweza kuingia sasa.";
                $smsSent = $smsService->sendSms($user->phone_no, $message);

                if (!$smsSent) {
                    Log::warning("Usajili wa Msimamizi {$user->email}: SMS imeshindwa kutumwa kwa {$user->phone_no}.");
                    // Fallback notification will be shown before redirecting
                } else {
                    Log::info("Usajili wa Msimamizi {$user->email}: SMS imetumwa kwa {$user->phone_no}.");
                }
            } catch (\Exception $smsException) {
                Log::error("Hitilafu kwenye Huduma ya SMS kwa {$user->phone_no}: " . $smsException->getMessage());
                // Fallback notification will be shown before redirecting
            }

            // For this flow, always go to success page, user can check SMS.
            // If SMS status was critical for notification type, could branch here.
            redirect(RegistrationSuccessPage::getUrl(['adminName' => $user->name]));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Filament form handles displaying these errors inline automatically.
            // Logging here is optional.
            Log::info('Usajili wa Msimamizi umeshindikana kutokana na makosa ya fomu.', $e->errors());
            Notification::make()
                ->title('Kuna Hitilafu Kwenye Taarifa')
                ->body('Tafadhali angalia taarifa ulizojaza na ujaribu tena.')
                ->danger()->send();
        } catch (\Exception $e) {
            Log::error("Usajili wa Msimamizi Umeshindikana (Kosa la Kipekee): " . $e->getMessage(), ['exception' => $e]);
            Notification::make()
                ->title('Usajili Umeshindikana')
                ->body('Samahani, kumetokea tatizo lisilotarajiwa. Tafadhali jaribu tena baadaye.')
                ->danger()->send();
        }
    }

    // Defines the submit button for the form
    protected function getFormActions(): array
    {
        return [
            Action::make('register') // Action name
                ->label('KAMILISHA USAJILI') // Swahili button text
                ->submit('submitRegistrationForm'), // Calls the PHP method above
        ];
    }

    // This forces the page to use Filament's simple, unauthenticated layout
    protected function getViewLayout(): string
    {
        return static::getSimpleLayout(); // Use Filament's static helper for simple layout
    }

    // Required if Simple Layout checks for panel logo configurations
    public static function HasLogo(): bool
    {
        return false; // We'll put a logo directly in our Blade view if needed
    }
}
