<?php

namespace App\Filament\Pages; // Or App\Filament\Admin\Pages

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
use App\Services\SmsService;
use App\Filament\Pages\RegistrationSuccessPage; // Assuming this class is named RegistrationSuccessPage
use Filament\Actions\Action;

class AdminSetupPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null; // Not in main navigation menu
    protected static string $view = 'filament.pages.admin-setup-page'; // Our Blade view for this page
    protected static ?string $title = 'Weka Msimamizi Mkuu wa Mfumo'; // "Setup System Administrator"
    protected static bool $shouldRegisterNavigation = false; // Does not show in sidebar

    // CUSTOM SLUG - This will make the URL /admin/weka-msimamizi (or your chosen slug)
    protected static ?string $slug = 'weka-msimamizi';

    public ?array $formData = [];

    public function mount(): void
    {
        // Only allow access if no admin user exists, OR if a specific query param is present (for testing)
        // This makes it a "first-time setup" page.
        if (User::role('admin')->exists() && !request()->query('allow_new_admin_setup')) {
            Notification::make()
                ->title('Ufikiaji Hauruhusiwi')
                ->body('Tayari kuna akaunti ya msimamizi. Ukurasa huu ni kwa ajili ya usanidi wa awali tu.')
                ->warning()
                ->send();
            redirect(filament()->getLoginUrl()); // Redirect to login
            return;
        }

        if (auth()->guard(config('filament.panels.admin.auth.guard'))->check()) {
            // If a non-admin user is somehow logged in, log them out or redirect
            auth()->guard(config('filament.panels.admin.auth.guard'))->logout();
            // Or simply redirect to login if already logged in by any means.
            // redirect(filament()->getLoginUrl());
            // return;
        }
        $this->form->fill();
    }

    // Specifies the simple, unauthenticated-style layout
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }

    // If simple layout component checks for panel logo context
    public function hasLogo(): bool
    {
        return false;
    }

    // Defines the registration form fields
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Jina Kamili la Msimamizi')->required()->autofocus()->maxLength(255),
                TextInput::make('email')->label('Barua Pepe')->email()->required()->maxLength(255)->unique(User::class, 'email')->helperText('Tumia barua pepe halali na ambayo haijatumika.'),
                TextInput::make('phone_no')->label('Namba ya Simu (anza na +255)')->tel()->required()->unique(User::class, 'phone_no')->helperText('Mfano: +255712345678. SMS itapokea hapa.'),
                TextInput::make('password')->label('Nenosiri Imara')->password()->required()->minLength(8)->confirmed()->helperText('Angalau herufi 8 kwa usalama zaidi.'),
                TextInput::make('password_confirmation')->label('Thibitisha Nenosiri')->password()->required()->minLength(8),
            ])
            ->statePath('formData');
    }

    // Handles form submission
    public function processAdminSetup(): void
    {
        // Prevent further setup if an admin already exists (double-check)
        if (User::role('admin')->exists() && !request()->query('allow_new_admin_setup')) {
             Notification::make()->title('Kosa')->body('Tayari kuna msimamizi.')->danger()->send();
             return;
        }

        $validatedData = $this->form->getState();
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_no' => $validatedData['phone_no'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => config('filament.panels.admin.auth.guard', 'web')]);
            $user->assignRole($adminRole);
            Log::info("INITIAL ADMIN SETUP: User {$user->email} created with admin role.");

            try {
                $smsService = app(SmsService::class);
                $appName = config('app.name', 'WakalaTel');
                $message = "Karibu {$user->name}! Akaunti yako ya Msimamizi Mkuu kwa {$appName} imeundwa. Sasa unaweza kuingia.";
                $smsService->sendSms($user->phone_no, $message);
            } catch (\Exception $smsE) { Log::error("SMS failed for initial admin {$user->email}: " . $smsE->getMessage()); }

            Notification::make()->title('Usanidi Umefanikiwa!')->body("Msimamizi Mkuu {$user->name} ameundwa.")->success()->send();
            // Redirect to RegistrationSuccess page or directly to Login
            redirect(RegistrationSuccessPage::getUrl(['adminName' => $user->name]));

        } catch (\Exception $e) {
            Log::error("Initial Admin Setup Error: " . $e->getMessage());
            Notification::make()->title('Usanidi Umeshindikana')->body('Tafadhali angalia kumbukumbu kwa maelezo zaidi.')->danger()->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submitSetup')->label('KAMILISHA USANIDI WA MSIMAMIZI')->submit('processAdminSetup'),
        ];
    }
}
