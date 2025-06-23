<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UserNotifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-notifications';

    protected static ?string $title = 'Taarifa Zako';

    protected static bool $shouldRegisterNavigation = false; // Not directly in sidebar


    protected static ?string $slug = 'taarifa-zangu';
}
