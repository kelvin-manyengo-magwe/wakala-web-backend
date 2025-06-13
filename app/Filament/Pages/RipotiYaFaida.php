<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\BusinessInvestment;
use App\Models\Shop;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form; // Make sure Form is imported

class RipotiYaFaida extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.ripoti-ya-faida';
    protected static ?string $navigationGroup = 'Ripoti na Takwimu';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Ripoti za Faida na Utendaji';

    // This will hold the data for the form. Name it descriptively.
    public ?array $dateFilterData = [];

    public float $totalInitialInvestment = 0;
    public float $currentTotalSystemFloat = 0;
    public float $currentTotalSystemCashEstimate = 0;
    public float $totalCommissionEarnedInPeriod = 0;
    public float $netProfitEstimate = 0;
    // public array $shopData = []; // Not used in current simplified report

    public static function getNavigationLabel(): string { return 'Ripoti za Faida'; }

    public function mount(): void
    {
        // Initialize form with default dates
        $this->form->fill([
            'startDate' => Carbon::now()->startOfMonth()->toDateString(),
            'endDate' => Carbon::now()->endOfDay()->toDateString(),
        ]);
        // Initial calculation based on default dates
        $this->startDate = $this->dateFilterData['startDate'] ?? Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = $this->dateFilterData['endDate'] ?? Carbon::now()->endOfDay()->toDateString();
        $this->calculateProfitReport();
    }

    /**
     * Standard method for defining a form on a Filament page.
     * This replaces `getFormSchema()` and your custom `profitReportForm()`
     */
    public function form(Form $form): Form // <<<< USE form() METHOD NAME
    {
        return $form
            ->schema([
                DatePicker::make('startDate')
                    ->label('Kuanzia Tarehe')
                    ->default(Carbon::now()->startOfMonth())
                    ->reactive() // Makes the form reactive to changes in this field
                    ->afterStateUpdated(fn () => $this->applyDateFilter()), // Call method on update

                DatePicker::make('endDate')
                    ->label('Hadi Tarehe')
                    ->default(Carbon::now()->endOfDay()) // Ensure it's end of day for correct between query
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->applyDateFilter()),
            ])
            ->columns(2) // Arrange date pickers side-by-side
            ->statePath('dateFilterData'); // Bind form data to $this->dateFilterData property
    }

    // Method to apply filter and recalculate
    public function applyDateFilter(): void
    {
        $this->startDate = $this->dateFilterData['startDate'] ?? null;
        $this->endDate = $this->dateFilterData['endDate'] ?? null;
        $this->calculateProfitReport();
    }

    public function calculateProfitReport(): void
    {
        $this->totalInitialInvestment = BusinessInvestment::sum('initial_investment_amount');

        $latestAirtelFloat = AirtelTransaction::orderBy('processed_at', 'desc')->value('float_balance');
        $latestHalotelFloat = HalotelTransaction::orderBy('processed_at', 'desc')->value('float_balance');
        $this->currentTotalSystemFloat = ($latestAirtelFloat ?? 0) + ($latestHalotelFloat ?? 0);

        $sDate = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : Carbon::now()->subYear()->startOfYear(); // Wider default if null
        $eDate = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay();

        // Assuming type_id 1 for deposit, 2 for withdrawal - THIS IS FRAGILE
        // Better to query TransactionType model for IDs
        $depositTypeId = \App\Models\TransactionType::where('name', 'deposit')->value('id') ?? 1;
        $withdrawalTypeId = \App\Models\TransactionType::where('name', 'withdrawal')->value('id') ?? 2;


        $this->totalCommissionEarnedInPeriod =
            AirtelTransaction::whereBetween('processed_at', [$sDate, $eDate])->sum('commission') +
            HalotelTransaction::whereBetween('processed_at', [$sDate, $eDate])->sum('commission');

        $openingCashAtStartOfBusiness = Shop::sum('initial_cash_on_hand');

        $depositsInPeriod = AirtelTransaction::where('type_id', $depositTypeId)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount') +
                            HalotelTransaction::where('type_id', $depositTypeId)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount');
        $withdrawalsInPeriod = AirtelTransaction::where('type_id', $withdrawalTypeId)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount') +
                               HalotelTransaction::where('type_id', $withdrawalTypeId)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount');

        // Rough estimate of total current cash assuming it started with initial shop cash
        // and changed by deposits (-) and withdrawals (+) from business cash perspective.
        // THIS IS A VERY SIMPLIFIED MODEL AND LIKELY NEEDS PROPER ACCOUNTING.
        $this->currentTotalSystemCashEstimate = $openingCashAtStartOfBusiness + $withdrawalsInPeriod - $depositsInPeriod;

        $currentBusinessValue = $this->currentTotalSystemFloat + $this->currentTotalSystemCashEstimate;
        $this->netProfitEstimate = $currentBusinessValue - $this->totalInitialInvestment;
    }
}
