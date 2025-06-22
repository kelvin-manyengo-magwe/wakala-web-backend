<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UwekezajiResource\Pages;
use App\Models\BusinessInvestment;
use App\Models\Shop; // To calculate distributions in table/view
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists; // <<<< IMPORT FOR INFOLISTS
use Filament\Infolists\Infolist; // <<<< IMPORT FOR INFOLISTS
use Filament\Infolists\Components\TextEntry; // <<<< IMPORT
use Filament\Infolists\Components\Section as InfolistSection; // <<<< IMPORT and alias
use Filament\Infolists\Components\RepeatableEntry; // <<<< IMPORT
use Filament\Infolists\Components\Grid as InfolistGrid; // <<<< IMPORT and alias
use Filament\Infolists\Components\KeyValueEntry; // <<<< IMPORT
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder; // For displaying calculated values in forms (if needed)
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection; // For working with collections

class UwekezajiResource extends Resource
  {
      protected static ?string $model = BusinessInvestment::class;
      protected static ?string $navigationIcon = 'heroicon-o-banknotes';
      protected static ?string $navigationGroup = 'Usimamizi wa Biashara'; // "Business Management"
      protected static ?int $navigationSort = 1; // First in group
      protected static ?string $recordTitleAttribute = 'investment_date'; // Use date for record title

        public static function getNavigationLabel(): string
        {
            return 'Uwekezaji na Mgawanyo'; // "Investment & Distribution"
        }
        public static function getPluralModelLabel(): string
        {
            return 'Uwekezaji wa Kuanzia'; // For List page title "Initial Investments"
        }
        public static function getModelLabel(): string
        {
            return 'Uwekezaji'; // For single item "Investment"
        }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('initial_investment_amount')
                    ->label('Jumla ya Uwekezaji (TZS)') // "Total Investment Amount"
                    ->numeric()->required()->prefix('Tsh')
                    ->minValue(1)
                    ->helperText('Kiasi chote cha pesa kilichowekwa kuanzisha biashara hii.'),
                DatePicker::make('investment_date')
                    ->label('Tarehe ya Uwekezaji') // "Investment Date"
                    ->required()->default(now())->maxDate(now()),
                Textarea::make('notes')
                    ->label('Maelezo ya Ziada (Hiari)') // "Additional Notes (Optional)"
                    ->columnSpanFull(),

                // Placeholder to show calculated distribution on EDIT form (read-only)
                // This is complex if one investment funds multiple shops over time.
                // For simplicity, we show the overall current allocation vs THIS investment.
                Forms\Components\Section::make('Muhtasari wa Mgawanyo (Makadirio)')
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit') // Only show on Edit
                    ->schema([
                        Placeholder::make('allocated_to_shops')
                            ->label('Jumla Iliyopelekwa Madukani (Taslimu na Float)')
                            ->content(function ($record) {
                                if (!$record) return 'Tsh 0.00';
                                $totalCashToShops = Shop::sum('initial_cash_on_hand');
                                $totalFloatToShops = Shop::all()->sum(fn($shop) => $shop->total_initial_float);
                                return 'Tsh ' . number_format($totalCashToShops + $totalFloatToShops, 2);
                            }),
                        Placeholder::make('remaining_from_this_investment')
                            ->label('Salio la Uwekezaji Huu (Makadirio)')
                            ->content(function ($record) {
                                if (!$record) return 'Tsh 0.00';
                                $totalCashToShops = Shop::sum('initial_cash_on_hand');
                                $totalFloatToShops = Shop::all()->sum(fn($shop) => $shop->total_initial_float);
                                $allocated = $totalCashToShops + $totalFloatToShops;
                                $remaining = $record->initial_investment_amount - $allocated;
                                return 'Tsh ' . number_format($remaining, 2);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('investment_date')->label('Tarehe')->date('d M Y')->sortable()->searchable(),
                TextColumn::make('initial_investment_amount')->label('Kiasi cha Uwekezaji')->money('TZS')->sortable()->badge()->color('success'),
                TextColumn::make('notes')->label('Maelezo')->words(10)->toggleable(isToggledHiddenByDefault: true)->searchable(),
                TextColumn::make('created_at')->label('Iliwekwa Lini')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([ /* Filters can be added here */ ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Angalia Taarifa'), // "View Details"
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([ Tables\Actions\BulkActionGroup::make([ Tables\Actions\DeleteBulkAction::make(), ]), ]);
    }

    // INFOLIST for the ViewUwekezaji Page
    public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            InfolistSection::make('Taarifa za Msingi za Uwekezaji')
                ->columns(2)
                ->schema([ /* ... amount, date, notes as before ... */ ]),

            InfolistSection::make('Muhtasari wa Mgawanyo wa Uwekezaji Mkuu')
                ->description('Huu ni muhtasari wa jumla kulingana na maduka yote yaliyosajiliwa.')
                ->schema([
                    TextEntry::make('total_initial_cash_in_shops')
                        ->label('Jumla ya Taslimu ya Kuanzia Madukani Pote')
                        ->money('TZS')
                        ->state(fn() => Shop::sum('initial_cash_on_hand')),

                    TextEntry::make('total_initial_float_in_shops')
                        ->label('Jumla ya Float ya Kuanzia Madukani Pote')
                        ->money('TZS')
                        ->state(fn() => Shop::all()->sum(fn($shop) => $shop->total_initial_float)), // Uses accessor

                    TextEntry::make('total_allocated_from_investment')
                        ->label('Jumla Iliyogawiwa Madukani (Taslimu + Float)')
                        ->money('TZS')
                        ->state(function() {
                            $cash = Shop::sum('initial_cash_on_hand');
                            $float = Shop::all()->sum(fn($shop) => $shop->total_initial_float);
                            return $cash + $float;
                        }),

                    TextEntry::make('remaining_from_this_investment_estimate')
                        ->label('Salio la Uwekezaji Huu (Baada ya Mgawanyo Wote)')
                        ->money('TZS')->color('warning')
                        ->state(function(BusinessInvestment $record) {
                            $cash = Shop::sum('initial_cash_on_hand');
                            $float = Shop::all()->sum(fn($shop) => $shop->total_initial_float);
                            return $record->initial_investment_amount - ($cash + $float);
                        }),

                    // Link to Shops resource to see individual shop details
                    Infolists\Components\Actions::make([
                        Infolists\Components\Actions\Action::make('view_shops_for_details')
                            ->label('Angalia Maduka kwa Ufafanuzi Zaidi')
                            ->url(DukaResource::getUrl('index')) // Link to your Shop resource list
                            ->icon('heroicon-o-building-storefront'),
                    ])->columnSpanFull(),
                ])->columns(1), // Or 2 for better layout of summary stats
        ]);
}


    public static function getRelations(): array
    {
        return [
            // No relations defined here, distribution is shown in infolist
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUwekezajis::route('/'),
            'create' => Pages\CreateUwekezaji::route('/create'),
            'view' => Pages\ViewUwekezaji::route('/{record}'),   // <<<< Ensure this line exists
            'edit' => Pages\EditUwekezaji::route('/{record}/edit'),
        ];
    }
}
