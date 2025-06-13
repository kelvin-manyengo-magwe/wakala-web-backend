<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DukaResource\Pages;
use App\Models\Shop; // Your Shop model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class DukaResource extends Resource
{
    protected static ?string $model = Shop::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Duka la Wakala';
    protected static ?string $pluralModelLabel = 'Maduka ya Wakala';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string { return 'Maduka na Salio'; }

    public static function form(Form $form): Form
    {
        $mnoOptions = ['airtel' => 'Airtel', 'halotel' => 'Halotel', 'tigo' => 'Tigo Pesa', 'mpesa' => 'M-Pesa'];

        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Jina la Duka/Eneo la Wakala')
                    ->required()->columnSpanFull(),
                TextInput::make('location')->label('Mahali Lilipo (Hiari)')->columnSpanFull(),
                TextInput::make('initial_cash_on_hand')
                    ->label('Fedha Taslimu Mkononi ya Kuanzia Dukani (TZS)')
                    ->numeric()->prefix('Tsh')->required()
                    ->helperText('Pesa taslimu iliyotolewa kwa ajili ya shughuli za duka hili.'),

                Repeater::make('mno_initial_allocations')
                    ->label('Mgawanyo wa Float kwa Mitandao (MNOs)')
                    ->addActionLabel('Ongeza Mtandao Mwingine')
                    ->columns(2)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($mnoOptions[$state['mno']] ?? 'Mtandao') . ': Float ' . number_format($state['initial_float_allocated'] ?? 0))
                    ->schema([
                        Select::make('mno')
                            ->label('Chagua Mtandao')
                            ->options($mnoOptions)
                            ->required()
                            ->distinct() // Prevent adding same MNO twice for same shop
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('initial_float_allocated')
                            ->label('Float ya Kuanzia (TZS)')
                            ->numeric()->prefix('Tsh')->required(false)->default(0),
                        // Optionally: Cash allocated specifically for this MNO's operations at this shop
                        // TextInput::make('initial_cash_for_mno_ops')
                        //     ->label('Taslimu kwa MNO hii (TZS)')
                        //     ->numeric()->prefix('Tsh')->required(false)->default(0),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Jina la Duka')->searchable()->sortable(),
                TextColumn::make('location')->label('Mahali'),
                TextColumn::make('initial_cash_on_hand')->label('Taslimu ya Kuanzia')->money('TZS')->sortable(),
                TextColumn::make('mno_allocations_summary')
                    ->label('Float za MNO (Kuanzia)')
                    ->getStateUsing(function (Shop $record) {
                        if (empty($record->mno_initial_allocations)) return 'Hakuna taarifa';
                        return collect($record->mno_initial_allocations)
                            ->map(fn ($alloc) => ucfirst($alloc['mno']) . ': ' . number_format($alloc['initial_float_allocated'] ?? 0))
                            ->implode(', ');
                    }),
            ])
            // ... actions, filters ...
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDukas::route('/'),
            'create' => Pages\CreateDuka::route('/create'),
            'edit' => Pages\EditDuka::route('/{record}/edit'),
        ];
    }
}
