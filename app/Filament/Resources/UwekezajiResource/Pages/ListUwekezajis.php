<?php

namespace App\Filament\Resources\UwekezajiResource\Pages;

use App\Filament\Resources\UwekezajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUwekezajis extends ListRecords
{
    protected static string $resource = UwekezajiResource::class;

          // ...
      public function getTitle(): string { return static::getResource()::getPluralModelLabel(); } // "Uwekezaji wa Kuanzia"


      protected function getHeaderActions(): array { return [ Actions\CreateAction::make()->label('Weka Uwekezaji Mpya'), ]; } // "Add New Investment"
}
