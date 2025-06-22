<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UwekezajiResource\Pages;
use App\Models\BusinessInvestment;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Filament\Resources\DukaResource; // For linking


class UwekezajiResource extends Resource
{
    protected static ?string $model = BusinessInvestment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'investment_date'; // Display formatted date if possible

    public static function getNavigationLabel(): string { return 'Uwekezaji & Mgawanyo'; } // "Investment & Distribution"
    public static function getPluralModelLabel(): string { return 'Rekodi za Uwekezaji'; } // "Investment Records"
    public static function getModelLabel(): string { return 'Uwekezaji'; } // "Investment"

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('initial_investment_amount')
                    ->label('Jumla ya Uwekezaji (TZS)')
                    ->numeric()->required()->prefix('Tsh')->minValue(1)
                    ->helperText('Kiasi chote cha pesa kilichowekwa kwa ajili ya biashara hii.'),
                DatePicker::make('investment_date')
                    ->label('Tarehe ya Uwekezaji')
                    ->required()->default(now())->maxDate(now()),
                Textarea::make('notes')->label('Maelezo ya Ziada (Hiari)')->columnSpanFull(),

                // Read-only Placeholder section for Edit Form (shows sums from related shops)
                Forms\Components\Section::make('Muhtasari wa Mgawanyo wa Uwekezaji Huu')
                    ->description('Hii inaonyesha jinsi uwekezaji huu ulivyotumika kuanzisha maduka.')
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->schema([
                        Placeholder::make('total_cash_to_funded_shops')
                            ->label('Jumla ya Taslimu Kwenye Maduka Yaliyofadhiliwa')
                            ->content(function (?BusinessInvestment $record) {
                                if (!$record) return 'Tsh 0.00';
                                return 'Tsh ' . number_format($record->shopsFunded()->sum('initial_cash_on_hand'), 2);
                            }),
                        Placeholder::make('total_float_to_funded_shops')
                            ->label('Jumla ya Float Kwenye Maduka Yaliyofadhiliwa')
                            ->content(function (?BusinessInvestment $record) {
                                if (!$record) return 'Tsh 0.00';
                                $totalFloat = 0;
                                $record->shopsFunded()->each(function (Shop $shop) use (&$totalFloat) {
                                    $totalFloat += $shop->total_initial_float; // Uses accessor
                                });
                                return 'Tsh ' . number_format($totalFloat, 2);
                            }),
                        Placeholder::make('total_allocated_from_this_investment')
                            ->label('Jumla Iliyogawiwa Kutoka Uwekezaji Huu')
                            ->content(function (?BusinessInvestment $record) {
                                if (!$record) return 'Tsh 0.00';
                                $cash = $record->shopsFunded()->sum('initial_cash_on_hand');
                                $float = 0;
                                $record->shopsFunded()->each(function (Shop $shop) use (&$float) {
                                    $float += $shop->total_initial_float;
                                });
                                return 'Tsh ' . number_format($cash + $float, 2);
                            }),
                        Placeholder::make('remaining_from_this_investment_direct')
                            ->label('Salio la Uwekezaji Huu (Baada ya Mgawanyo)')
                            ->content(function (?BusinessInvestment $record) {
                                if (!$record) return 'Tsh 0.00';
                                $cash = $record->shopsFunded()->sum('initial_cash_on_hand');
                                $float = 0;
                                $record->shopsFunded()->each(function (Shop $shop) use (&$float) {
                                    $float += $shop->total_initial_float;
                                });
                                return 'Tsh ' . number_format($record->initial_investment_amount - ($cash + $float), 2);
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
                // Sum of cash allocated to shops FROM THIS INVESTMENT
                TextColumn::make('shops_funded_sum_initial_cash_on_hand')
                    ->sum('shopsFunded', 'initial_cash_on_hand') // Uses relationship for sum
                    ->label('Taslimu Madukani')->money('TZS')->badge()->color('info'),
                // Custom sum for initial float
                TextColumn::make('shops_funded_sum_initial_float')
                    ->label('Float Madukani')
                    ->money('TZS')
                    ->state(function (BusinessInvestment $record): float {
                        $totalFloat = 0;
                        foreach ($record->shopsFunded as $shop) { // Eager load shopsFunded if possible via query
                            $totalFloat += $shop->total_initial_float;
                        }
                        return $totalFloat;
                    })->badge()->color('warning'),
                TextColumn::make('notes')->label('Maelezo')->words(8)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([ /* ... */ ])
            ->actions([ Tables\Actions\ViewAction::make()->label('Angalia'), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), ])
            ->bulkActions([ Tables\Actions\BulkActionGroup::make([ Tables\Actions\DeleteBulkAction::make(), ]), ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                InfolistSection::make('Taarifa za Msingi za Uwekezaji')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('initial_investment_amount')->label('Kiasi Kilichowekwa')->money('TZS')->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('investment_date')->label('Tarehe ya Uwekezaji')->date('d F Y'),
                        TextEntry::make('notes')->label('Maelezo ya Ziada')->columnSpanFull()->placeholder('Hakuna maelezo.'),
                    ]),

                InfolistSection::make('Maduka Yaliyofadhiliwa na Uwekezaji Huu')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('shopsFunded') // NAME OF THE RELATIONSHIP
                            ->label('')
                            // getStateUsing is not needed if 'shopsFunded' is a direct relationship.
                            // Filament's RepeatableEntry will iterate over $record->shopsFunded automatically.
                            ->grid(2)
                            ->schema([
                                InfolistGrid::make(1)
                                    ->extraAttributes(['class' => 'p-4 border dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-800 space-y-3 mb-4'])
                                    ->schema([
                                        ImageEntry::make('image_url') // From Shop model accessor, called on each shop in the relation
                                            ->label('') ->height(120)->width('100%')
                                            ->extraAttributes(['class' => 'object-cover rounded-md mb-2 bg-gray-50 dark:bg-gray-700'])
                                            ->defaultImageUrl(asset('images/placeholder_shop.png')),
                                        TextEntry::make('name')->label('Jina la Duka')->weight('bold')->size(TextEntry\TextEntrySize::Medium)
                                            ->url(fn (Shop $record): string => DukaResource::getUrl('edit', ['record' => $record])), // Link to edit Shop
                                        TextEntry::make('location')->label('Mahali'),
                                        TextEntry::make('initial_cash_on_hand')->label('Taslimu ya Kuanzia')->money('TZS')->badge()->color('sky'),
                                        // Display MNO allocations from the JSON field of the Shop model
                                        KeyValueEntry::make('mno_initial_allocations') // THIS IS THE JSON FIELD NAME
                                            ->label('Float za MNO (Kuanzia)')
                                            // ->valueAsArray() // Sometimes needed for KeyValue on JSON
                                            ->state(function(Shop $record) { // $record here is the Shop instance from the shopsFunded relation
                                                return collect($record->mno_initial_allocations ?? [])
                                                    ->mapWithKeys(fn($alloc) => [
                                                        ucfirst($alloc['mno_key'] ?? 'MNO') => number_format($alloc['initial_float'] ?? 0) . ' TZS'
                                                    ])->all();
                                            }),
                                        TextEntry::make('assignedWakalas.name') // Names of Wakalas assigned to this Shop
                                            ->label('Mawakala wa Duka Hili')
                                            ->badge()->separator(', ')
                                            ->placeholder('Hakuna wakala.'),
                                    ]),
                            ]),

                        // Summary for THIS investment
                        InfolistGrid::make(2)->schema([
                            TextEntry::make('total_allocated_from_this_investment_calc')
                                ->label('Jumla Iliyogawiwa na Uwekezaji Huu')
                                ->money('TZS')
                                ->state(function(BusinessInvestment $record){
                                    $total = 0;
                                    foreach($record->shopsFunded as $shop){ // shopsFunded IS THE RELATIONSHIP
                                        $total += $shop->initial_cash_on_hand;
                                        foreach($shop->mno_initial_allocations ?? [] as $alloc) {
                                            $total += (float) ($alloc['initial_float'] ?? 0);
                                        }
                                    }
                                    return $total;
                                }),
                            TextEntry::make('remaining_from_this_investment_calc')
                                ->label('Salio Baki la Uwekezaji Huu')
                                ->money('TZS')->color('warning')
                                ->state(function(BusinessInvestment $record){
                                    $totalAllocated = 0;
                                     foreach($record->shopsFunded as $shop){
                                        $totalAllocated += $shop->initial_cash_on_hand;
                                        foreach($shop->mno_initial_allocations ?? [] as $alloc) {
                                            $totalAllocated += (float) ($alloc['initial_float'] ?? 0);
                                        }
                                    }
                                    return $record->initial_investment_amount - $totalAllocated;
                                }),
                        ])->columnSpanFull()->extraAttributes(['class' => 'mt-4 p-4 border-t dark:border-gray-700']),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUwekezajis::route('/'),
            'create' => Pages\CreateUwekezaji::route('/create'),
            'view' => Pages\ViewUwekezaji::route('/{record}'),
            'edit' => Pages\EditUwekezaji::route('/{record}/edit'),
        ];
    }
}
