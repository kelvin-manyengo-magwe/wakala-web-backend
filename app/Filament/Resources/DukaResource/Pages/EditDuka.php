<?php

namespace App\Filament\Resources\DukaResource\Pages;

use App\Filament\Resources\DukaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Device;


class EditDuka extends EditRecord
{
    protected static string $resource = DukaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
          {
              $selectedDeviceIds = $this->data['devices'] ?? []; // 'devices' is the name of the Select field for edit

              // Devices that should be associated with this shop
              Device::whereIn('id', $selectedDeviceIds)->update(['shop_id' => $this->record->id]);

              // Devices that were previously associated but are no longer selected for THIS shop
              // Set their shop_id to null (unassign them from this shop)
              Device::where('shop_id', $this->record->id)
                    ->whereNotIn('id', $selectedDeviceIds)
                    ->update(['shop_id' => null]);
          }
}
