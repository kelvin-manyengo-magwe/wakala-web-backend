<?php

namespace App\Filament\Pages; // Or App\Filament\Admin\Pages if appropriate

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page; // Base Filament Page class
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService; // Your SMS Service
use App\Filament\Pages\RegistrationSuccess; // Your custom success page class
use Filament\Actions\Action;

class AdminRegistration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null; // Not in navigation menu
    protected static string $view = 'filament.pages.admin-registration'; // Path to the Blade view for this page
    protected static ?string $title = 'Jisajili Kama Msimamizi Mkuu'; // Swahili Page Title
    protected static bool $shouldRegisterNavigation = false; // Don't show in the main sidebar

    // This slug, combined with the panel path, defines the URL.
    // For /admin/register, this MUST be 'register'.
    protected static ?string $slug = 'register';

    public ?array $formData = []; // Holds the form data

    public function mount(): void
    {
        // If a user is already logged into this panel's guard, redirect them
        if (auth()->guard(config('filament.panels.admin.auth.guard'))->check()) {
            redirect(config('filament.panels.admin.home_url')); // Redirect to the panel's home
        }
        $this->form->fill(); // Initialize the form
    }

    // This method explicitly tells Filament to use its simple, unauthenticated-style layout
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple'; // CORRECTED: Path to simple layout
    }

    // This method can prevent errors if the simple layout attempts to render a panel logo
    // by checking $this->hasLogo() on the page instance.
    public function hasLogo(): bool
    {
        return false; // Indicate this specific page instance does not provide a panel logo
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Jina Kamili')->required()->autofocus()->maxLength(255),
                TextInput::make('email')->label('Barua Pepe')->email()->required()->maxLength(255)->unique(User::class, 'email')->helperText('Tumia barua pepe halali na ambayo haijatumika.'),
                TextInput::make('phone_no')->label('Namba ya Simu (anza na +255)')->tel()->required()->helperText('Mfano: +255712345678. SMS itapokea hapa.'),
                TextInput::make('password')->label('Nenosiri Imara')->password()->required()->minLength(8)->confirmed()->helperText('Angalau herufi 8 kwa usalama zaidi.'),
                TextInput::make('password_confirmation')->label('Thibitisha Nenosiri')->password()->required()->minLength(8),
            ])
            ->statePath('formData');
    }

    public function submitRegistrationForm(): void // Method called by the form action
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
            Log::info("Admin user registered: {$user->email}");

            // SMS Logic
            try {
                $smsService = app(SmsService::class);
                $appName = config('app.name', 'WakalaTel'); // Assuming this or WakalaTel
                $message = "Karibu {$user->name} kwenye {$appName}! Akaunti yako ya Usimamizi Mkuu imeundwa.";
                $smsService->sendSms($user->phone_no, $message);
                Log::info("Admin registration SMS attempt for {$user->phone_no}.");
            } catch (\Exception $smsException) {
                Log::error("SMS Service Exception (Admin Reg) for {$user->phone_no}: " . $smsException->getMessage());
            }

            Notification::make()->title('Usajili Umefanikiwa!')->body("Hongera {$user->name}! Akaunti yako imeundwa.")->success()->send(); // No ->persistent() needed before redirect usually
            redirect(RegistrationSuccess::getUrl(['adminName' => $user->name]));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are displayed on the form. Log if needed.
        } catch (\Exception $e) {
            Log::error("Admin Registration Error: " . $e->getMessage());
            Notification::make()->title('Usajili Umeshindikana')->body('Tatizo la kiufundi limetokea.')->danger()->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register') // Internal name of the action
                ->label('KAMILISHA USAJILI') // Swahili Button Text
                ->submit('submitRegistrationForm'), // Calls the submitRegistrationForm() method
        ];
    }
}
