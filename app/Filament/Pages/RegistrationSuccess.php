<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;

class RegistrationSuccess extends Page
{
    protected static ?string $title = 'Hongera! Usajili Umekamilika';
    protected static string $view = 'filament.pages.registration-success'; // Blade view name
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'karibu-admin'; // Example slug for URL: /admin/karibu-admin

    public ?string $adminName = 'Msimamizi';

    public function mount(): void
    {
        $this->adminName = request()->query('adminName', 'Msimamizi Mpendwa');
        // if (auth()->guard(config('filament.panels.admin.auth.guard'))->check()) {
        //      redirect(config('filament.panels.admin.home_url'));
        // }
        // ^^ No need to redirect logged in users from success page if it's truly only for guests
    }

    protected function getViewData(): array { return ['adminName' => $this->adminName]; }
    public function getLayout(): string { return 'filament-panels::components.layout.simple'; }
    public static function hasLogo(): bool { return false; }
}
