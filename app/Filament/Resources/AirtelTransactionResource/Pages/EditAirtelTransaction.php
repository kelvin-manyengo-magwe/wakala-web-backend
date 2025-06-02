<?php

namespace App\Filament\Resources\AirtelTransactionResource\Pages;

use App\Filament\Resources\AirtelTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAirtelTransaction extends EditRecord
{
    protected static string $resource = AirtelTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
