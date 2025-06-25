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
// Ensure RegistrationSuccess page exists and its route is correctly defined
// We will use its route name for redirection.

class AdminRegistration extends Page implements HasForms
{
    use InteractsWithForms;

    // This is where Filament will look for the Blade view file
    protected static string $view = 'filament-panels::pages.auth.register';

    // These properties are typically for pages registered WITHIN a panel's navigation.
    // For a standalone page like this, they have less effect but are good to define.
    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;

    // Title that might be used by the layout or browser tab
    protected static ?string $title = 'Jisajili Msimamizi Mkuu';

    // This slug IS NOT directly used by the route defined in web.php,
    // but if Filament tries to generate a URL for this page internally, it would use it.
    // The route in web.php (e.g., /uanzisha-msimamizi) is what users will access.
    protected static ?string $slug = 'custom-admin-registration-page'; // Keep unique

    public ?array $formData = []; // Holds the live data from the form

    public function mount(): void
    {
        // The 'guest' middleware on the route in web.php handles redirecting logged-in users.
        // This is an additional check in case the middleware somehow doesn't fire as expected.
        if (auth()->guard(config('filament.auth.guard', 'web'))->check()) {
             redirect(config('filament.home_url', '/admin')); // Or appropriate dashboard URL
        }
        $this->form->fill(); // Initialize form with empty data or defaults from schema
    }

    // This is the crucial method to tell Filament to use its simple, card-like layout
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }

    // Method to satisfy the SimpleLayoutComponent if it checks for a logo on the page class
    public function hasLogo(): bool
    {
        return false; // Our registration page typically doesn't display the main panel logo
    }

    // Define the registration form
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Jina Kamili la Msimamizi') // "Admin's Full Name"
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Barua Pepe') // "Email"
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email') // Check for uniqueness in the users table
                    ->helperText('Tumia barua pepe halali na ambayo haijatumika.'),
                TextInput::make('phone_no')
                    ->label('Namba ya Simu (anza na +255)') // "Phone Number (start with +255)"
                    ->tel()
                    ->required()
                  //  ->unique(User::class, 'phone_no') // Check for uniqueness
                    ->helperText('Mfano: +255712345678. SMS itapokea hapa.'),
                TextInput::make('password')
                    ->label('Nenosiri Imara') // "Strong Password"
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->confirmed() // This automatically adds a 'password_confirmation' rule
                    ->helperText('Nenosiri liwe na angalau herufi 8 kwa usalama.'),
                TextInput::make('password_confirmation')
                    ->label('Thibitisha Nenosiri') // "Confirm Password"
                    ->password()
                    ->required() // Required due to `confirmed()` on the password field
                    ->minLength(8),
            ])
            ->statePath('formData'); // This binds the form data to the $this->formData public property
    }

    // Method to handle the registration form submission
    public function handleAdminRegistration(): void // Renamed for clarity
    {
        $validatedData = $this->form->getState(); // Get and validate form data

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_no' => $validatedData['phone_no'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Find or create the 'admin' role for the 'web' guard (or your panel's guard)
            $adminRole = Role::firstOrCreate(
                ['name' => 'admin'],
                ['guard_name' => config('filament.auth.guard', 'web')]
            );
            $user->assignRole($adminRole);

            Log::info("Msimamizi mpya (admin) amesajiliwa: {$user->name}, {$user->email}, Simu: {$user->phone_no}");

            // Attempt to send the welcome SMS
            try {
                $smsService = app(SmsService::class); // Resolve using service container

                $message = "Karibu sana Bwana/Bibi {$user->name} kwenye mfumo wa WakalaTel! Akaunti yako ya Usimamizi Mkuu imeundwa na ipo tayari kutumika. Furahia huduma!";

                if ($smsService->sendSms($user->phone_no, $message)) {
                    Log::info("SMS ya kukaribisha imetumwa kwa msimamizi {$user->name} namba {$user->phone_no}.");
                } else {
                    Log::warning("SMS ya kukaribisha kwa msimamizi {$user->name} IMEGOMA kutumwa kwa {$user->phone_no} (lakini mtumiaji ameundwa).");
                }
            } catch (\Exception $smsException) {
                Log::error("Hitilafu kwenye Huduma ya SMS kwa {$user->phone_no} (usajili wa msimamizi): " . $smsException->getMessage());
                // Optionally notify user here that SMS failed but account created
            }

            // Send a success notification within Filament (will show briefly before redirect)
            Notification::make()
                ->title('Usajili Umekamilika!')
                ->body("Hongera sana {$user->name}! Akaunti yako ya msimamizi imeundwa. Karibu!")
                ->success()
                ->send();

            // Redirect to your custom "Registration Success" page (ensure route exists)
            // We pass the adminName to personalize the success message
            redirect()->route('custom.admin.registration.success', ['adminName' => $user->name]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Filament forms usually display validation errors automatically next to fields.
            // You can log this if you want, but usually not needed to send a separate Notification.
            Log::debug('Usajili wa Msimamizi umeshindikana (Validation Errors): ', $e->errors());
        } catch (\Exception $e) {
            // Catch any other unexpected errors during user/role creation or SMS
            Log::error("Hitilafu ya Jumla Wakati wa Usajili wa Msimamizi: " . $e->getMessage(), ['exception_trace' => $e->getTraceAsString()]);
            Notification::make()
                ->title('Usajili Umeshindikana Vibaya')
                ->body('Samahani, kumetokea tatizo la kiufundi lisilotarajiwa. Tafadhali jaribu tena baadaye au wasiliana na wasimamizi wa mfumo.')
                ->danger()
                ->send();
        }
    }

    // Defines the submit button(s) for the form.
    protected function getFormActions(): array
    {
        return [
            Action::make('register') // Internal name for the action
                ->label('KAMILISHA USAJILI') // Button's Swahili text
                ->submit('handleAdminRegistration') // Calls the public method defined above
                ->color('danger')
            ->button()

        ];
    }

    public function getCachedFormActions()
        {
            return $this->getFormActions();
        }

        public function hasFullWidthFormActions(): bool
{
    return true; // or false, depending on your design needs
}
}
