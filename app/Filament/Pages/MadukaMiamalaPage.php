<?php

namespace App\Filament\Pages; // Or App\Filament\Admin\Pages if namespaced

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms; // For the shop filter form
use Filament\Forms\Contracts\HasForms;         // For the shop filter form
use Filament\Forms\Form;
use App\Models\Shop;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
// Add other MNO Transaction models as you create them
use Illuminate\Support\Collection; // For initializing transaction collections
use Carbon\Carbon; // For date formatting if you do it here, though Blade is doing it

class MadukaMiamalaPage extends Page implements HasForms // REMOVED HasTable
{
    use InteractsWithForms; // Trait for the shop selection form
    // REMOVED: use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Changed icon slightly
    protected static string $view = 'filament.pages.maduka-miamala-page';
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 22;
    protected static ?string $title = 'Miamala ya Maduka';

    // Data for the shop filter form
    public ?array $shopFilterData = [
        'selectedShopId' => null, // Initialize
    ];

    // Public properties to hold transactions for Blade view
    public Collection $airtelShopTransactions;
    public Collection $halotelShopTransactions;
    // public Collection $mpesaShopTransactions; // etc. for other MNOs

    // Property to hold the name of the selected shop for display
    public ?string $displaySelectedShopName = null;


    public static function getNavigationLabel(): string
    {
        return 'Maduka na Miamala';
    }

    public function mount(): void
    {
        // Initialize collections as empty
        $this->airtelShopTransactions = new Collection();
        $this->halotelShopTransactions = new Collection();
        // $this->mpesaShopTransactions = new Collection();

        // Fill the form which will set $this->shopFilterData correctly.
        // Since 'selectedShopId' in shopFilterData is null by default, loadShopTransactions will handle it.
        $this->form->fill();
        // Initial load attempt (will be empty as no shop is selected)
        $this->loadShopTransactions();
    }

    // This is THE form for this page, used for the shop filter
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedShopId') // Field name is 'selectedShopId'
                    ->label('Chagua Duka')
                    ->options(Shop::orderBy('name')->pluck('name', 'id')->all()) // Ordered shops
                    ->searchable()
                    ->live() // Crucial for triggering update
                    ->placeholder('Tafadhali chagua duka...')
                    ->helperText('Chagua duka ili kuona miamala yake husika.'),
            ])
            ->statePath('shopFilterData'); // All fields in this form will be under $this->shopFilterData
    }

    // This Livewire lifecycle hook is automatically called when a 'live' property
    // inside $shopFilterData (like 'selectedShopId') is updated.
    public function updatedShopFilterDataSelectedShopId($value): void
    {
        // The $value here is the new shop ID
        // $this->shopFilterData['selectedShopId'] is already updated by Livewire
        $this->loadShopTransactions(); // Re-fetch transactions for the newly selected shop
    }

    public function loadShopTransactions(): void
    {
        $shopIdToLoad = $this->shopFilterData['selectedShopId'] ?? null;

        if (!$shopIdToLoad) {
            $this->airtelShopTransactions = new Collection();
            $this->halotelShopTransactions = new Collection();
            $this->displaySelectedShopName = null;
            // Reset other MNO transaction collections...
            return;
        }

        $selectedShopInstance = Shop::find($shopIdToLoad);
        $this->displaySelectedShopName = $selectedShopInstance?->name;

        $this->airtelShopTransactions = AirtelTransaction::where('shop_id', $shopIdToLoad)
            ->with(['user', 'customer', 'type'])
            ->latest('processed_at')
            ->get();

        $this->halotelShopTransactions = HalotelTransaction::where('shop_id', $shopIdToLoad)
            ->with(['user', 'customer', 'type'])
            ->latest('processed_at')
            ->get();

        // Load for other MNOs and store in their respective public properties
        // e.g., $this->mpesaShopTransactions = MpesaTransaction::where('shop_id', $shopIdToLoad)->...->get();
    }

    // No getTableQuery() or table() method definition needed if not using HasTable/InteractsWithTable traits
    // to render a MAIN Filament Table on this page. We are rendering simple HTML tables in Blade.

    // Data passed to the Blade view automatically includes public properties.
    // If you need extra transformations for Blade, you can use this, but not strictly necessary
    // for displaySelectedShopName as it's now a public property.
    // protected function getViewData(): array
    // {
    //     return [
    //         'currentShopName' => $this->displaySelectedShopName,
    //     ];
    // }
}
