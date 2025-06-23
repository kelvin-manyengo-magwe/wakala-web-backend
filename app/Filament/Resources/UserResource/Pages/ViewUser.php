<?php
namespace App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord; // Or ListRecords

class ViewUser extends ViewRecord // Or ListRecords if you redirected to 'index'
{
    protected static string $resource = UserResource::class;
    protected static string $view = 'filament.resources.user-resource.pages.view-user'; // OR .list-users

    // For Swahili title on view page
    public function getTitle(): string
    {
        return 'Angalia Taarifa za Wakala: ' . $this->record->name;
    }

    protected function getHeaderActions(): array { return [ Actions\EditAction::make()->label('Hariri Wakala'), ]; }
}
