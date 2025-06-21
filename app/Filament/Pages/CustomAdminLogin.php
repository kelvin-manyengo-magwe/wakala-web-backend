<?php
namespace App\Filament\Pages; // Or App\Filament\Admin\Pages if you placed it there

use Filament\Pages\Auth\Login as BaseLogin; // Extend Filament's own Login page
use Filament\Actions\Action;
  // Your custom registration page class
use App\Filament\Pages\AdminSetupPage;

class CustomAdminLogin extends BaseLogin
{
    // By default, it will use Filament's standard login view.
    // (resources/views/vendor/filament-panels/pages/auth/login.blade.php if published)

    // Override the method that Filament's login page uses to create the "Register" action (link)
    protected function getRegisterAction(): Action // This generates the "Register" link
{
    $panelId = filament()->getCurrentPanel()->getId();
    // Link to your new AdminSetupPage instead of AdminRegistration
    return Action::make('register')
        ->link()
        ->label('Weka Msimamizi Mkuu') // "Setup Head Administrator"
        ->url(AdminSetupPage::getUrl(panel: $panelId)); // <<< POINTS TO YOUR NEW PAGE URL
}

    // Important: Make sure this login page also uses a simple layout
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }

    // And signals no panel logo is typically shown on auth pages
    public static function panelHasLogo(): bool
    {
        return false;
    }
}
