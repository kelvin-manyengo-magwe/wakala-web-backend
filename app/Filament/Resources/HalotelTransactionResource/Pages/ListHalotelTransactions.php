<?php

namespace App\Filament\Resources\HalotelTransactionResource\Pages;

use App\Filament\Resources\HalotelTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHalotelTransactions extends ListRecords
{
    protected static string $resource = HalotelTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // Kitufe kimeondolewa
        ];
    }

    public function getTitle(): string // Kichwa cha ukurasa kwa Kiswahili
    {
        return 'Miamala ya Halotel';
    }
}
