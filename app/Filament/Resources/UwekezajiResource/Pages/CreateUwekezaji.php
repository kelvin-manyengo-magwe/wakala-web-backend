<?php

namespace App\Filament\Resources\UwekezajiResource\Pages;

use App\Filament\Resources\UwekezajiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUwekezaji extends CreateRecord
{
    protected static string $resource = UwekezajiResource::class;

    public function getTitle(): string
          {
                return 'Weka Taarifa za Uwekezaji Mpya';
          }
}
