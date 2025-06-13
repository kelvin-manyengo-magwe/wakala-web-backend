<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\BusinessInvestment;
use App\Models\DailyShopReport; // This would be the ideal model
use App\Models\Shop;
use App\Models\AirtelTransaction; // To get current live floats for all MNOs across shops
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker; // For date range filters
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;


class RipotiYaFaida extends Page implements HasForms // Implement HasForms
{
    use InteractsWithForms; // Use trait for form interaction

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.ripoti-ya-faida';
    protected static ?string $navigationGroup = 'Ripoti na Takwimu';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Ripoti za Faida na Utendaji';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public float $totalInitialInvestment = 0;
    public float $currentTotalSystemFloat = 0;
    public float $currentTotalSystemCashEstimate = 0; // This will be more complex
    public float $totalCommissionEarnedInPeriod = 0;
    public float $netProfitEstimate = 0;
    public array $shopData = []; // To hold data per shop

    public static function getNavigationLabel(): string { return 'Ripoti za Faida'; }

    public function mount(): void
    {
        $this->form->fill([ // Fill form with default dates
            'startDate' => Carbon::now()->startOfMonth()->toDateString(),
            'endDate' => Carbon::now()->endOfDay()->toDateString(),
        ]);
        $this->calculateProfitReport(); // Initial calculation
    }

    protected function getFormSchema(): array // Use getFormSchema for non-full page forms in v3
    {
        return [
            DatePicker::make('startDate')->label('Kuanzia Tarehe')->default(Carbon::now()->startOfMonth())->reactive(),
            DatePicker::make('endDate')->label('Hadi Tarehe')->default(Carbon::now())->reactive(),
        ];
    }

    // This method replaces the `form()` method when using `getFormSchema()`
    public function profitReportForm(Form $form): Form
    {
        return $form->schema($this->getFormSchema())->statePath('dateFilterData');
    }

    public function updatedDateFilterData($value): void // updated($propertyName) convention
    {
        $this->startDate = $this->dateFilterData['startDate'] ?? null;
        $this->endDate = $this->dateFilterData['endDate'] ?? null;
        $this->calculateProfitReport();
    }


    public function calculateProfitReport(): void
    {
        $this->totalInitialInvestment = BusinessInvestment::sum('initial_investment_amount');

        // Get CURRENT overall system float from latest MNO transactions across all wakala/users
        $latestAirtelFloat = AirtelTransaction::orderBy('processed_at', 'desc')->value('float_balance');
        $latestHalotelFloat = HalotelTransaction::orderBy('processed_at', 'desc')->value('float_balance');
        // Add other MNOs similarly...
        $this->currentTotalSystemFloat = ($latestAirtelFloat ?? 0) + ($latestHalotelFloat ?? 0);


        // --- Cash & Commission in Selected Period ---
        $sDate = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $eDate = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay();

        $this->totalCommissionEarnedInPeriod =
            AirtelTransaction::whereBetween('processed_at', [$sDate, $eDate])->sum('commission') +
            HalotelTransaction::whereBetween('processed_at', [$sDate, $eDate])->sum('commission');

        // Estimate current total cash in business. This is complex!
        // Simplistic: Initial cash in shops + total deposits - total withdrawals (for the period, then adjust from opening).
        // This really needs proper daily EOD cash reconciliations.
        // For now, let's show an example if we had DailyShopReport
        $openingCashAtStartOfPeriod = Shop::sum('initial_cash_on_hand'); // Assuming this is the base
        $depositsInPeriod = AirtelTransaction::where('type_id', /*depositId*/1)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount') +
                            HalotelTransaction::where('type_id', /*depositId*/1)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount');
        $withdrawalsInPeriod = AirtelTransaction::where('type_id', /*withdrawalId*/2)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount') +
                               HalotelTransaction::where('type_id', /*withdrawalId*/2)->whereBetween('processed_at', [$sDate, $eDate])->sum('amount');

        // THIS IS A VERY ROUGH ESTIMATE of current cash across the business for selected period
        $this->currentTotalSystemCashEstimate = $openingCashAtStartOfPeriod + $depositsInPeriod - $withdrawalsInPeriod;
                                           // Needs more robust tracking from day one of business for overall cash

        // Total current business value estimate
        $currentBusinessValue = $this->currentTotalSystemFloat + $this->currentTotalSystemCashEstimate;

        // Simplistic overall Profit since business inception based on current values
        // This requires robust calculation of *total money that has come into cash* vs *total money that left float to become cash*
        // True profit is (Total Current Value (Float + Cash)) - Initial Investment + (Adjustments if any)
        // OR (Total Revenue (e.g. Commissions)) - (Total Operational Costs - not tracked yet)

        // Profit for the PERIOD is simpler: Total Commissions for Period +/- (Change in Cash Value for Period) +/- (Change in Float Value for Period)
        // For this example, we use overall:
        $this->netProfitEstimate = $currentBusinessValue - $this->totalInitialInvestment;
                                  // This is not truly "period profit" unless business started in period.
    }
}
