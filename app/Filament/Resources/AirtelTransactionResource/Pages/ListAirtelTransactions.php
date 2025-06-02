<?php

namespace App\Filament\Resources\AirtelTransactionResource\Pages;

use App\Filament\Resources\AirtelTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAirtelTransactions extends ListRecords
{
    protected static string $resource = AirtelTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // Tumeondoa kitufe cha "New Miamala..."
        ];
    }

    public function getTitle(): string // Kuweka kichwa cha ukurasa kwa Kiswahili
    {
        return 'Miamala ya Airtel'; // Au unaweza kutumia static::getResource()::getPluralModelLabel(); ikiwa Resource inatoa Kiswahili moja kwa moja
    }
}
