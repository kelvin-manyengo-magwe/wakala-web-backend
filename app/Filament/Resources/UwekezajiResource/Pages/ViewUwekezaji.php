<?php

namespace App\Filament\Resources\UwekezajiResource\Pages;

use App\Filament\Resources\UwekezajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord; // Base class for viewing a record

class ViewUwekezaji extends ViewRecord
{
    protected static string $resource = UwekezajiResource::class;
    //protected static string $view = 'filament.resources.uwekezaji-resource.pages.view-uwekezaji'; // Default if view not specified below


    // Swahili for the page title
    public function getTitle(): string
    {
        return __('Angalia Taarifa za Uwekezaji'); // "View Investment Details"
    }

    // Add an Edit button to the header of the View page
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Hariri Uwekezaji Huu'), // "Edit This Investment"
        ];
    }



}
