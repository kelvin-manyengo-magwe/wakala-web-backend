<?php

namespace App\Filament\Pages; // Adjust if needed

use Filament\Pages\Page;

class RegistrationSuccess extends Page
{
    protected static ?string $title = 'Hongera! Usajili Umekamilika';
    protected static string $view = 'filament.pages.registration-success-page';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'usajili-mafanikio'; // URL e.g., /admin/usajili-mafanikio

    public ?string $adminName = 'Msimamizi'; // Default name

    public function mount(): void
    {
        $this->adminName = request()->query('adminName', 'Msimamizi Mpendwa');
        if (auth()->guard(config('filament.auth.guard'))->check()) {
             redirect(config('filament.home_url'));
        }
    }

    protected function getViewData(): array
    {
        return [
            'adminName' => $this->adminName,
        ];
    }

    protected function getViewLayout(): string
    {
        return static::getSimpleLayout();
    }

    public static function panelHasLogo(): bool
    {
        return false;
    }
}
