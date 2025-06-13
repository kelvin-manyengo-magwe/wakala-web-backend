<?php
namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Models\BusinessInvestment; // Assuming this model exists

class UwekezajiKuanzia extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.uwekezaji-kuanzia'; // Blade view for the page
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Uwekezaji wa Kuanzia';

    public ?array $data = []; // To hold form data

    public static function getNavigationLabel(): string
    {
        return 'Uwekezaji wa Kuanzia';
    }

    public function mount(): void
    {
        // Load existing investment if any (assuming only one primary investment record for now)
        $investment = BusinessInvestment::first();
        if ($investment) {
            $this->form->fill($investment->toArray());
        } else {
            $this->form->fill(['investment_date' => now()->toDateString()]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('initial_investment_amount')
                    ->label('Jumla ya Uwekezaji wa Kuanzia (TZS)')
                    ->numeric()
                    ->required()
                    ->prefix('Tsh')
                    ->helperText('Kiasi chote cha pesa kilichowekwa kuanzisha biashara.'),
                DatePicker::make('investment_date')
                    ->label('Tarehe ya Uwekezaji')
                    ->required()
                    ->default(now()),
                Textarea::make('notes')
                    ->label('Maelezo ya Ziada (Hiari)')
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model(BusinessInvestment::class); // For saving/loading simplicity with one record
    }

    public function saveInvestment(): void
    {
        try {
            $data = $this->form->getState();
            // Assuming you only have one main investment record or want to update the first one.
            // For multiple, you'd need a resource or more complex logic.
            BusinessInvestment::updateOrCreate(['id' => BusinessInvestment::first()?->id ?? null], $data);

            Notification::make()
                ->title('Uwekezaji Umehifadhiwa')
                ->body('Taarifa za uwekezaji wa kuanzia zimehifadhiwa kikamilifu.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Imeshindikana Kuhifadhi')
                ->body('Kumekuwa na tatizo: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
