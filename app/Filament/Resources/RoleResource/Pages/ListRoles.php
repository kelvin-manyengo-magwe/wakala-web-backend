<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    /*protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }*/

    public function getTitle(): string { return 'Orodha ya Majukumu ya Watumiaji'; }

    protected function getHeaderActions(): array
    {
      return
            [
              Actions\CreateAction::make()->label('Unda Jukumu Jipya la Mtumiaji'),
            ];
     }
}
