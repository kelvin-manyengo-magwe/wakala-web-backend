<?php

namespace App\Filament\Resources\DukaResource\Pages;

use App\Filament\Resources\DukaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Device; // Ensure your Device model is imported for relationship syncing

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
        // Extract the IDs for the 'assignedWakalas' relationship.
        // 'assignedWakalas_create_ids' is the name of your Select field for the create form.
        if (isset($data['assignedWakalas_create_ids'])) {
            $this->syncableWakalas = (array) $data['assignedWakalas_create_ids'];
            unset($data['assignedWakalas_create_ids']); // Remove it so it's not passed to Shop::create()
        }

        // Extract the IDs for the 'devices' relationship.
        // 'devices_create_ids' is the name of your Select field for the create form.
        if (isset($data['devices_create_ids'])) {
            $this->syncableDevices = (array) $data['devices_create_ids'];
            unset($data['devices_create_ids']); // Remove it
        }

        // Ensure defaults for fields that might be missing if not filled but have DB defaults or are fillable
        $data['initial_cash_on_hand'] = $data['initial_cash_on_hand'] ?? 0;
        $data['mno_initial_allocations'] = $data['mno_initial_allocations'] ?? [];
        $data['is_active'] = $data['is_active'] ?? true;

        return $data; // Return the modified data for Shop creation
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
