<?php

namespace App\Filament\Resources\DukaResource\Pages;

use App\Filament\Resources\DukaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Device; // Ensure your Device model is imported for relationship syncing
use App\Models\BusinessInvestment;



class CreateDuka extends CreateRecord
{
    protected static string $resource = DukaResource::class;

    // Temporary properties to hold IDs for relationships from the form.
    // These will be populated in mutateFormDataBeforeCreate and used in afterCreate.
    protected array $syncableWakalas = [];
    protected array $syncableDevices = [];

    public function getTitle(): string
    {
        return 'Tengeneza Duka Jipya la Wakala'; // "Create New Agent Shop"
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the index page of this resource after creation
        return $this->getResource()::getUrl('index');
    }

    /**
     * This hook is called with the form data AFTER validation but BEFORE
     * the main Eloquent model (Shop) is created.
     * We use it to extract relationship data and remove those temporary keys
     * from the data that will be used to create the Shop itself.
     */
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['funding_source_type']) && $data['funding_source_type'] === 'new_investment') {
            if (!empty($data['new_investment_amount'])) {
                $newInvestment = BusinessInvestment::create([
                    'initial_investment_amount' => $data['new_investment_amount'],
                    'investment_date' => now(),
                    'notes' => 'Uwekezaji wa kipekee kwa duka: ' . ($data['name'] ?? 'Duka Jipya'),
                ]);
                $data['business_investment_id'] = $newInvestment->id;
            }
        }

        unset($data['funding_source_type'], $data['new_investment_amount']);

        if (isset($data['assignedWakalas'])) {
            $this->syncableWakalas = (array) $data['assignedWakalas'];
            unset($data['assignedWakalas']);
        }
        if (isset($data['devices'])) {
            $this->syncableDevices = (array) $data['devices'];
            unset($data['devices']);
        }

        return $data;
    }

    /**
     * This hook is called AFTER the main Shop record ($this->record) has been successfully created.
     * We use the IDs stored from mutateFormDataBeforeCreate to sync the relationships.
     */
    protected function afterCreate(): void
    {
        // $this->record is the newly created Shop instance.

        // Sync the 'assignedWakalas' BelongsToMany relationship
        if (!empty($this->syncableDevices)) { // syncableDevices gets populated from 'devices_create_ids'
                    Device::whereIn('id', $this->syncableDevices)->update(['shop_id' => $this->record->id]);
            }

        // Sync the 'devices' relationship
        if (!empty($this->syncableDevices)) {
            // This logic depends on how Shop and Device are related:
            // OPTION 1: If Shop hasMany Devices (Device model has shop_id foreign key)
            // AND your Select field 'devices_create_ids' provides Device IDs.
            // Device::whereIn('id', $this->syncableDevices)->update(['shop_id' => $this->record->id]);

            // OPTION 2: If Shop BelongsToMany Devices (through a pivot table, e.g., 'device_shop')
            // Assumes 'devices' is the name of the BelongsToMany relationship method in Shop.php
            if (method_exists($this->record, 'devices') &&
                $this->record->devices() instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                 $this->record->devices()->sync($this->syncableDevices);
            } elseif (method_exists($this->record, 'devices') &&
                      $this->record->devices() instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                // Handle HasMany if your Device selection implies updating device.shop_id
                 Device::whereIn('id', $this->syncableDevices)->update(['shop_id' => $this->record->id]);
            }
        }
    }
}
