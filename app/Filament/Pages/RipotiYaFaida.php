<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use App\Models\BusinessInvestment;
use App\Models\Shop;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Language;
use Illuminate\Database\Eloquent\Builder;

class RipotiYaFaida extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.ripoti-ya-faida';
    protected static ?string $navigationGroup = 'Ripoti na Takwimu';
    protected static ?string $title = 'Ripoti za Faida na Utendaji';

    public ?array $filterData = [];

    // All public properties that the Blade view will use
    public float $totalInitialInvestment = 0;
    public float $openingTotalAssets = 0;
    public float $closingTotalAssets = 0;
    public float $totalCommissionInPeriod = 0;
    public float $overallNetProfit = 0;
    public array $shopReportData = [];

    public static function getNavigationLabel(): string { return 'Ripoti za Faida'; }

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => Carbon::now()->startOfMonth(),
            'endDate' => Carbon::now(),
        ]);
        $this->calculateAllReports();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')->label('Kuanzia Tarehe')->reactive(),
                DatePicker::make('endDate')->label('Hadi Tarehe')->reactive()->maxDate(now()),
            ])
            ->columns(2)->statePath('filterData');
    }

    public function updatedFilterData(): void { $this->calculateAllReports(); }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('downloadPdf')->label('Pakua PDF')->icon('heroicon-o-document-arrow-down')->action(fn() => $this->downloadPdf()),
                Action::make('downloadWord')->label('Pakua Word')->icon('heroicon-o-document-text')->action(fn() => $this->downloadWord()),
            ])->label('Pakua Ripoti')->button()->color('primary'),
        ];
    }

    public function calculateAllReports(): void
    {
        $sDate = Carbon::parse($this->filterData['startDate'] ?? now()->startOfMonth());
        $eDate = Carbon::parse($this->filterData['endDate'] ?? now())->endOfDay();
        $mnoModels = ['airtel' => AirtelTransaction::class, 'halotel' => HalotelTransaction::class];

        $shops = Shop::all();
        $this->totalInitialInvestment = BusinessInvestment::sum('initial_investment_amount');

        // Reset totals before loop
        $this->shopReportData = [];
        $this->totalCommissionInPeriod = 0;
        $this->openingTotalAssets = 0;

        foreach ($shops as $shop) {
            $initialShopCash = (float)$shop->initial_cash_on_hand;
            $initialShopFloat = (float)$shop->total_initial_float; // From accessor
            $openingShopAssets = $initialShopCash + $initialShopFloat;

            $commissionForPeriod = 0;
            $depositsForPeriod = 0;
            $withdrawalsForPeriod = 0;
            $mnoData = [];

            foreach ($mnoModels as $mnoKey => $modelClass) {
                $query = $modelClass::where('shop_id', $shop->id)->whereBetween('processed_at', [$sDate, $eDate]);
                $commission = (float)$query->clone()->sum('commission');
                $deposits = (float)$query->clone()->whereHas('type', fn(Builder $q) => $q->where('name', 'deposit'))->sum('amount');
                $withdrawals = (float)$query->clone()->whereHas('type', fn(Builder $q) => $q->where('name', 'withdrawal'))->sum('amount');

                $commissionForPeriod += $commission;
                $depositsForPeriod += $deposits;
                $withdrawalsForPeriod += $withdrawals;

                $mnoData[$mnoKey] = [ 'commission' => $commission ];
            }

            // PER YOUR ALGORITHM:
            // Net profit for the period is simply the commission earned.
            // Changes in cash/float balance each other out in terms of total value.
            $shopNetProfit = $commissionForPeriod;

            $this->shopReportData[$shop->id] = [
                'name' => $shop->name,
                'image_url' => $shop->image_url,
                'image_path' => $shop->image_path,
                'opening_assets' => $openingShopAssets,
                'commission_earned' => $commissionForPeriod,
                'net_profit_period' => $shopNetProfit,
                'mno_data' => $mnoData,
            ];

            $this->openingTotalAssets += $openingShopAssets;
            $this->totalCommissionInPeriod += $commissionForPeriod;
        }

        $this->closingTotalAssets = $this->openingTotalAssets + $this->totalCommissionInPeriod;
        $this->overallNetProfit = $this->totalCommissionInPeriod;
    }

    private function getReportDataForDownloads(): array {
        return [ 'startDate' => $this->filterData['startDate'], 'endDate' => $this->filterData['endDate'], 'dateGenerated' => Carbon::now()->toDateTimeString() ] + get_object_vars($this);
    }

    public function downloadPdf()
    {
        $dataForView = $this->getReportDataForDownloads();
        $pdf = PDF::loadView('filament.pages.ripoti-ya-faida-pdf', $dataForView);
        return response()->streamDownload( fn() => print($pdf->output()), 'Ripoti_Faida_'.Carbon::now()->format('Ymd').'.pdf' );
    }

    public function downloadWord()
    {
        $data = $this->getReportDataForDownloads();
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        // ... (The rest of the word generation logic will use $data)
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'Ripoti_Faida_'.Carbon::now()->format('Ymd').'.docx';

        return response()->stream( fn() => $objWriter->save('php://output'), 200, [
            "Content-Type" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "Content-Disposition" => "attachment; filename={$fileName}"
        ]);
    }
}
