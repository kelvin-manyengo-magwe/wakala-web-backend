<?php

namespace App\Filament\Resources\DukaResource\Pages;

use App\Filament\Resources\DukaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDuka extends CreateRecord
{
    protected static string $resource = DukaResource::class;

    public function getTitle(): string
    {
        return 'Tengeneza Duka Jipya la Wakala';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // This hook is called after the main record is created
    protected function afterCreate(): void
    {
        // Sync the 'assignedWakalas' relationship using data from 'assignedWakalas_create_ids'
        $selectedWakalasIds = $this->data['assignedWakalas_create_ids'] ?? [];
        if (!empty($selectedWakalasIds)) {
            $this->record->assignedWakalas()->sync($selectedWakalasIds);
        }

        // Sync the 'devices' relationship using data from 'devices_create_ids'
        $selectedDeviceIds = $this->data['devices_create_ids'] ?? [];
        if (!empty($selectedDeviceIds)) {
            // Assuming 'devices' is a BelongsToMany relationship on the Shop model
            // If it's HasMany (where devices.shop_id is a foreign key), you'd do:
            // \App\Models\Device::whereIn('id', $selectedDeviceIds)->update(['shop_id' => $this->record->id]);
            $this->record->devices()->sync($selectedDeviceIds); // Use this if devices is BelongsToMany
        }
    }
}
