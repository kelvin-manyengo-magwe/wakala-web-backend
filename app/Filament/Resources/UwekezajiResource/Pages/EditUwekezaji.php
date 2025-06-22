<?php

namespace App\Filament\Resources\UwekezajiResource\Pages;

use App\Filament\Resources\UwekezajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUwekezaji extends EditRecord
{
    protected static string $resource = UwekezajiResource::class;


      public function getTitle(): string
          {
              return 'Hariri Taarifa za Uwekezaji';
          } 


      protected function getHeaderActions(): array
          {
              return [ Actions\DeleteAction::make(), Actions\ViewAction::make()->label('Angalia'), ];
        }
}
