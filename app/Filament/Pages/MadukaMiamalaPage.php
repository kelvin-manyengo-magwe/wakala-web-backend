<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker; // For date filtering
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use App\Models\Shop;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
// ... other MNO Transaction Models ...
use Illuminate\Support\Collection as SupportCollection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class MadukaMiamalaPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list'; // Updated icon
    protected static string $view = 'filament.pages.maduka-miamala-page';
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 23;
    protected static ?string $title = 'Miamala kwa Duka';

    // --- STATE PROPERTIES for the form ---
    public ?string $selectedShopId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    // --- Properties to hold transaction lists ---
    public SupportCollection $airtelShopTransactionsList;
    public SupportCollection $halotelShopTransactionsList;
    // ... other MNO lists ...

    public ?string $displaySelectedShopName = null;

    public static function getNavigationLabel(): string { return 'Miamala kwa Duka'; }

    public function mount(): void
    {
        $this->airtelShopTransactionsList = new SupportCollection();
        $this->halotelShopTransactionsList = new SupportCollection();

        // Set default date range for filter (e.g., this month)
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();

        $this->form->fill([ // Initialize form fields with current property values
            'selectedShopId' => $this->selectedShopId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
        // $this->loadShopTransactions(); // Let Livewire's updated hook trigger first load if needed, or explicit button
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedShopId')
                    ->label('Chagua Duka')
                    ->options(Shop::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->live()->placeholder('Tafadhali chagua duka...'),
                DatePicker::make('startDate')->label('Kuanzia Tarehe')->live()->maxDate(fn () => $this->endDate ?? now()),
                DatePicker::make('endDate')->label('Hadi Tarehe')->live()->default(now())->minDate(fn () => $this->startDate ?? null),
            ])
            ->columns(3); // Layout filters in a row
    }


            public function getTransactionTypeInfo(string $typeName): array
          {
              $type = strtolower($typeName);

              if ($type === 'deposit') {
                  return ['label' => 'Kuweka', 'color' => 'success'];
              }

              if ($type === 'withdrawal') {
                  return ['label' => 'Kutoa', 'color' => 'danger'];
              }

              return ['label' => ucfirst($type), 'color' => 'gray'];
              }

    // Livewire hooks for when filter properties change
    public function updatedSelectedShopId($value): void { $this->loadShopTransactions(); }
    public function updatedStartDate($value): void { $this->loadShopTransactions(); }
    public function updatedEndDate($value): void { $this->loadShopTransactions(); }

    public function loadShopTransactions(): void
    {
        Log::info("Kupakia miamala kwa Duka ID: {$this->selectedShopId}, Kuanzia: {$this->startDate}, Hadi: {$this->endDate}");

        if (!$this->selectedShopId) {
            $this->airtelShopTransactionsList = new SupportCollection();
            $this->halotelShopTransactionsList = new SupportCollection();
            $this->displaySelectedShopName = 'Tafadhali Chagua Duka';
            return;
        }

        $selectedShopInstance = Shop::find($this->selectedShopId);
        $this->displaySelectedShopName = $selectedShopInstance?->name ?? 'Duka Halijulikani';

        $startDateFilter = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $endDateFilter = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay(); // Default end to today if not set

        $applyDateFilters = function (Builder $query) use ($startDateFilter, $endDateFilter) {
            return $query
                ->when($startDateFilter, fn(Builder $q) => $q->where('processed_at', '>=', $startDateFilter))
                ->when($endDateFilter, fn(Builder $q) => $q->where('processed_at', '<=', $endDateFilter));
        };

        // Helper for querying to reduce repetition
        $getTransactionsForMno = function (string $modelClass) use ($applyDateFilters) {
            return $modelClass::query()
                ->where('shop_id', $this->selectedShopId)
                ->when($this->startDate || $this->endDate, $applyDateFilters) // Apply date filters if any are set
                ->with(['user', 'customer', 'type'])
                ->latest('processed_at')
                ->take(200) // Limiting results for performance
                ->get();
        };

        $this->airtelShopTransactionsList = $getTransactionsForMno(AirtelTransaction::class);
        $this->halotelShopTransactionsList = $getTransactionsForMno(HalotelTransaction::class);

        Log::info("Miamala ya Airtel iliyopakiwa: " . $this->airtelShopTransactionsList->count());
        Log::info("Miamala ya Halotel iliyopakiwa: " . $this->halotelShopTransactionsList->count());
    }
}
