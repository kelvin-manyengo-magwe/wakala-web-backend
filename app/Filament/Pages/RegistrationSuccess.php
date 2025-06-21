<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;

class RegistrationSuccess extends Page // Class name is RegistrationSuccess
{
    protected static ?string $title = 'Hongera! Usajili Umekamilika';
    protected static string $view = 'filament.pages.registration-success'; // Blade view path
    protected static bool $shouldRegisterNavigation = false;

    // Needs a unique slug for its own URL: /admin/shukrani-usajili-admin
    protected static ?string $slug = 'shukrani-usajili-admin';

    public ?string $adminName = 'Msimamizi Mkuu';

    public function mount(): void
    {
        $this->adminName = request()->query('adminName', $this->adminName);
        if (auth()->guard(config('filament.panels.admin.auth.guard'))->check()) {
             redirect(config('filament.panels.admin.home_url'));
        }
    }

    protected function getViewData(): array { return ['adminName' => $this->adminName]; }

    public function getLayout(): string {
            return 'filament-panels::components.layout.simple';
          }

    public static function hasLogo(): bool {
            return false;
          }
}
