<?php

namespace App\Filament\Resources\HalotelTransactionResource\Pages;

use App\Filament\Resources\HalotelTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHalotelTransaction extends EditRecord
{
    protected static string $resource = HalotelTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
