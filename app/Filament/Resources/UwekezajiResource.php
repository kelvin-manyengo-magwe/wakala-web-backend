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
use Illuminate\Support\Facades\Log;


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
            Forms\Components\Section::make('Taarifa za Msingi za Uwekezaji')
                ->schema([
                    TextInput::make('initial_investment_amount')->label('Jumla ya Uwekezaji (TZS)')->numeric()->required()->prefix('Tsh')->minValue(0.01)->helperText('Kiasi chote cha pesa kilichowekwa.'),
                    DatePicker::make('investment_date')->label('Tarehe ya Uwekezaji')->required()->default(now())->maxDate(now()),
                    Textarea::make('notes')->label('Maelezo ya Ziada (Hiari)')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Muhtasari wa Mgawanyo wa Uwekezaji Huu')
                ->description('Hii huonyesha jinsi uwekezaji huu ulivyotumika kwa maduka yanayohusishwa nao. Husasishwa baada ya kuhifadhi.')
                ->columns(2)
                ->visible(fn (string $operation): bool => $operation === 'edit') // Show only on edit
                ->schema([
                    Placeholder::make('allocated_cash_summary')
                        ->label('Jumla Taslimu kwa Maduka Husika')
                        ->content(function (?BusinessInvestment $record): string {
                            if (!$record || !$record->relationLoaded('shopsFunded')) {
                                $record?->loadMissing('shopsFunded'); // Load if not already loaded
                            }
                            return 'Tsh ' . number_format($record?->shopsFunded->sum('initial_cash_on_hand') ?? 0, 2);
                        }),
                    Placeholder::make('allocated_float_summary')
                        ->label('Jumla Float kwa Maduka Husika')
                        ->content(function (?BusinessInvestment $record): string {
                            if (!$record || !$record->relationLoaded('shopsFunded')) {
                                $record?->loadMissing('shopsFunded');
                            }
                            $totalFloat = 0;
                            if ($record && $record->shopsFunded) {
                                foreach ($record->shopsFunded as $shop) {
                                    $totalFloat += $shop->total_initial_float; // Uses accessor from Shop model
                                }
                            }
                            return 'Tsh ' . number_format($totalFloat, 2);
                        }),
                    Placeholder::make('total_allocated_summary')
                        ->label('Jumla Iliyogawiwa Kutoka Huu Uwekezaji')
                        ->content(function (?BusinessInvestment $record): string {
                            if (!$record || !$record->relationLoaded('shopsFunded')) {
                                $record?->loadMissing('shopsFunded');
                            }
                            $cash = $record?->shopsFunded->sum('initial_cash_on_hand') ?? 0;
                            $float = 0;
                            if ($record && $record->shopsFunded) {
                                foreach($record->shopsFunded as $shop) { $float += $shop->total_initial_float; }
                            }
                            return 'Tsh ' . number_format($cash + $float, 2);
                        }),
                    Placeholder::make('remaining_from_investment_summary')
                        ->label('Salio la Uwekezaji Huu')
                        ->content(function (?BusinessInvestment $record): string {
                            if (!$record) return 'Tsh 0.00'; // No record, no calculation
                            if (!$record->relationLoaded('shopsFunded')) {
                                $record->loadMissing('shopsFunded');
                            }
                            $cash = $record->shopsFunded->sum('initial_cash_on_hand');
                            $float = 0;
                            foreach($record->shopsFunded as $shop) { $float += $shop->total_initial_float; }
                            $allocated = $cash + $float;
                            return 'Tsh ' . number_format($record->initial_investment_amount - $allocated, 2);
                        }),
                ]),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->query(BusinessInvestment::query()->with('shopsFunded')) // Eager load shopsFunded
        ->columns([
            TextColumn::make('investment_date')->label('Tarehe')->date('d M Y')->sortable()->searchable(),
            TextColumn::make('initial_investment_amount')->label('Kiasi cha Uwekezaji')->money('TZS')->sortable()->badge()->color('success'),
            TextColumn::make('shops_funded_count')->counts('shopsFunded')->label('Maduka Yaliyofadhiliwa')->badge()->color('primary'),
            TextColumn::make('total_cash_to_shops')
                ->label('Taslimu Madukani Jumla')
                ->money('TZS')->badge()->color('info')
                ->state(function (BusinessInvestment $record): float {
                    return (float) $record->shopsFunded->sum('initial_cash_on_hand');
                })->sortable(false), // Disable sort on calculated sum for now

                TextColumn::make('total_float_to_shops')
                        ->label('Jumla ya Floti Madukani')
                        ->money('TZS')->badge()->color('warning')
                        ->state(function (BusinessInvestment $record): float {
                            // IMPROVED & FIXED: This now works because the accessor in Shop.php is correct.
                            // We use the more concise sum() method on the collection.
                            return $record->shopsFunded->sum('total_initial_float');
                })->sortable(false), // Disabling sort

            TextColumn::make('notes')->label('Maelezo')->words(8)->toggleable(isToggledHiddenByDefault:true)->searchable(),
        ])
        ->filters([ /* ... */ ])
        ->actions([ Tables\Actions\ViewAction::make()->label('Angalia'), Tables\Actions\EditAction::make()->label('Hariri'), ])
        ->bulkActions([ /* ... */ ]);
}

public static function infolist(Infolist $infolist): Infolist
{
    // Reusing $mnoOptions definition logic from DukaResource if available or define here.
    $mnoDisplayOptions = ['airtel' => 'Airtel Money', 'halotel' => 'Halopesa', 'tigo' => 'Tigo Pesa', 'mpesa' => 'M-Pesa (Vodacom)'];

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
                    RepeatableEntry::make('shopsFunded') // Uses shopsFunded() HasMany relationship
                        ->label('')
                        ->grid(2) // 2 Shops per row in the infolist display
                        ->schema([
                            // Schema for each Shop entry
                            InfolistGrid::make(1) // Each shop details in a single column grid
                                ->extraAttributes(['class' => 'p-4 my-2 border dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-800 space-y-2'])
                                ->schema([
                                    ImageEntry::make('image_url') // Uses $shop->image_url accessor
                                        ->label('') ->height(120)->defaultImageUrl(asset('images/placeholder_shop.png')),
                                    TextEntry::make('name')->label('Jina la Duka')->weight('bold')
                                        ->url(fn (Shop $record): string => DukaResource::getUrl('edit', ['record' => $record])),
                                    TextEntry::make('location')->label('Mahali')->icon('heroicon-s-map-pin'),
                                    TextEntry::make('initial_cash_on_hand')->label('Taslimu ya Kuanzia')->money('TZS')->badge()->color('sky'),
                                    // Displaying MNO allocations correctly
                                    KeyValueEntry::make('mno_initial_allocations') // Shop model's JSON attribute
                                        ->label('Float za MNO za Kuanzia')
                                        ->state(function(Shop $record) use ($mnoDisplayOptions) { // $record is a Shop here
                                            return collect($record->mno_initial_allocations ?? [])
                                                ->mapWithKeys(function($alloc) use ($mnoDisplayOptions) {
                                                    // Key inside JSON is 'mno_key', value is 'initial_float'
                                                    $mnoName = $mnoDisplayOptions[$alloc['mno_key']] ?? ucfirst($alloc['mno_key'] ?? 'MNO');
                                                    return [$mnoName => number_format($alloc['initial_float'] ?? 0) . ' TZS'];
                                                })->all();
                                        }),
                                    TextEntry::make('total_initial_float') // Using the Shop accessor directly
                                        ->label('Jumla ya Float Kuanzia (Dukani)')
                                        ->money('TZS')->badge()->color('success'),
                                    TextEntry::make('assignedWakalas.name')->label('Mawakala wa Duka')->badge()->separator(', ')->placeholder('--'),
                                ]),
                        ]),

                    // Overall Summary for THIS investment's funded shops
                    InfolistGrid::make(2)->schema([
                        TextEntry::make('calculated_total_cash_from_investment')
                            ->label('Jumla ya Taslimu Iliyogawiwa na Uwekezaji Huu')
                            ->money('TZS')
                            ->state(function(BusinessInvestment $record){
                                return $record->shopsFunded->sum('initial_cash_on_hand');
                            }),
                        TextEntry::make('calculated_total_float_from_investment')
                            ->label('Jumla ya Float Iliyogawiwa na Uwekezaji Huu')
                            ->money('TZS')
                            ->state(function(BusinessInvestment $record){
                                $totalFloat = 0;
                                foreach($record->shopsFunded as $shop){
                                    $totalFloat += $shop->total_initial_float; // Use accessor
                                }
                                return $totalFloat;
                            }),
                        TextEntry::make('calculated_overall_allocated')
                            ->label('Jumla Kuu Iliyogawiwa na Uwekezaji Huu')
                            ->money('TZS')
                            ->state(function(BusinessInvestment $record){
                                  $totalCash = $record->shopsFunded->sum('initial_cash_on_hand');
                                  $totalFloat = 0;
                                   foreach($record->shopsFunded as $shop){ $totalFloat += $shop->total_initial_float; }
                                  return $totalCash + $totalFloat;
                              }),
                        TextEntry::make('calculated_remaining_from_investment')
                            ->label('Salio la Uwekezaji Huu')
                            ->money('TZS')->color('warning')
                            ->state(function(BusinessInvestment $record){
                                $totalCash = $record->shopsFunded->sum('initial_cash_on_hand');
                                $totalFloat = 0;
                                 foreach($record->shopsFunded as $shop){ $totalFloat += $shop->total_initial_float; }
                                return $record->initial_investment_amount - ($totalCash + $totalFloat);
                            }),
                    ])->columnSpanFull()->extraAttributes(['class' => 'mt-6 pt-4 border-t dark:border-gray-700']),
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
