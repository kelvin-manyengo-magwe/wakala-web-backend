<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DukaResource\Pages;
use App\Models\Shop;
use App\Models\User;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use App\Models\BusinessInvestment;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;





class DukaResource extends Resource
{
    protected static ?string $model = Shop::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Usimamizi wa Biashara';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Duka la Wakala';
    protected static ?string $pluralModelLabel = 'Maduka ya Wakala';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Maduka na Salio';
    }

    public static function form(Form $form): Form
    {
        $mnoOptions = ['airtel' => 'Airtel', 'halotel' => 'Halotel', 'tigo' => 'Tigo Pesa', 'mpesa' => 'M-Pesa'];

        $isEditPage = fn (Livewire $livewire): bool => $livewire->operation === 'edit'; // More reliable way to check operation

        return $form->schema([
            Forms\Components\Section::make('Taarifa Kuu za Duka')
                ->columns(1)
                ->schema([
                    TextInput::make('name')->label('Jina la Duka/Eneo la Wakala')->columnSpanFull(),


                    Grid::make(1)->schema([
                     Forms\Components\Radio::make('funding_source_type')
                         ->label('Chagua Chanzo cha Mtaji')
                         ->options([
                             'existing_investment' => 'Tumia Uwekezaji Uliopo',
                             'new_investment' => 'Weka Kiasi cha Kipekee kwa Duka Hili',
                         ])
                         // **THE FIX**: We REMOVE ->inline() to make the options vertical.
                         ->default('existing_investment')->live(),
                 ])->columnSpanFull()->extraAttributes(['class' => 'mt-4']), // Add top margin for spacing

                 // We wrap the conditional fields in another Grid to give them proper spacing.
                 Grid::make(1)->schema([
                     Select::make('business_investment_id')
                         ->label('Chagua Uwekezaji Uliopo')
                         ->searchable()->preload()
                         ->options(function () {
                             // The working options logic from before
                             return BusinessInvestment::with('shopsFunded')->get()
                                 ->filter(fn ($investment) => $investment->remaining_amount > 1)
                                 ->mapWithKeys(function ($investment) {
                                     $label = sprintf(
                                         "Tarehe: %s | Salio Baki: %s TZS",
                                         $investment->investment_date->format('d M Y'),
                                         number_format($investment->remaining_amount, 2)
                                     );
                                     return [$investment->id => $label];
                                 });
                         })
                         ->placeholder('Tafadhali chagua uwekezaji...')
                         ->visible(fn (\Filament\Forms\Get $get): bool => $get('funding_source_type') === 'existing_investment'),

                     TextInput::make('new_investment_amount')
                         ->label('Andika Kiasi cha Uwekezaji wa Kipekee')
                         ->numeric()->prefix('Tsh')->minValue(1)
                         ->helperText('Weka kiasi cha pesa kwa ajili ya duka hili pekee.')
                         ->visible(fn (\Filament\Forms\Get $get): bool => $get('funding_source_type') === 'new_investment'),
                 ])->columnSpanFull()->extraAttributes(['class' => 'mt-2']), // Add a little space




                    FileUpload::make('image_path') // Field name matches database column
                            ->label('Picha ya Duka (Hiari)') // "Shop Image (Optional)"
                            ->image() // Specify that it's an image for validation and preview
                            ->disk('public') // Which filesystem disk to use (from config/filesystems.php)
                            ->directory('shop-images') // Subdirectory within the public disk's root
                            ->nullable()  // if the image is not must to be uploaded.
                            ->visibility('public') // Make uploaded files publicly accessible
                            ->imageEditor() // Optional: enable basic image editor
                            ->imagePreviewHeight('200')
                            ->maxSize(2048) // Max file size in KB (e.g., 2MB)
                            ->helperText('Weka picha ya duka kwa utambulisho bora.')
                            ->columnSpanFull(), // Take full width if in 2-column layout

                    TextInput::make('location')->label('Mahali Lilipo (Hiari)')->columnSpanFull(),
                    TextInput::make('initial_cash_on_hand')->label('Fedha Taslimu Mkononi ya Kuanzia Dukani (TZS)')->numeric()->prefix('Tsh')->default(0),
                    Toggle::make('is_active')->label('Duka Lipo Kazini?')->default(true)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Mgawanyo wa Float kwa Mitandao (MNOs)')
                ->schema([ /* ... Repeater for mno_initial_allocations as before ... */
                    Repeater::make('mno_initial_allocations')
                        // ... (same repeater schema as previously corrected) ...
                        ->label('')
                        ->addActionLabel('Ongeza Mtandao Mwingine')
                        ->columns(2)->collapsible()->itemLabel(fn(array $state): ?string =>
                            ($mnoOptions[$state['mno_key']] ?? 'Mtandao') . ': Float ' . number_format($state['initial_float_allocated'] ?? 0)
                        )->schema([
                            Select::make('mno_key')
                                ->label('Chagua Mtandao')
                                ->options($mnoOptions)
                                ->required()->distinct()->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            TextInput::make('initial_float_allocated')
                                ->label('Float ya Kuanzia (TZS)')->numeric()->prefix('Tsh')->default(0),
                        ])->columnSpanFull(),
                ]),

                Forms\Components\Section::make('Wakala Watakaohudumu Dukani')
                ->schema([
                    Select::make('assignedWakalas') // The name must match the relationship in the Shop model
                        ->label('Chagua Wakala (au Wakala Wengi)')
                        ->relationship(
                            name: 'assignedWakalas',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'wakala'))
                )
                ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                ->multiple()
                ->preload()
                ->searchable()
                ->helperText('Chagua watumiaji wenye jukumu la "wakala".')
                ->columnSpanFull()
                        ->visible(fn (string $operation) => $operation === 'create'), // ONLY on create

                    // Field for EDIT page
                    Select::make('assignedWakalas') // Matches relationship name for auto-binding
                        ->label('Chagua Wakala (au Wakala Wengi)')
                        ->relationship(
                            name: 'assignedWakalas',
                            titleAttribute: 'name', // Used for displaying existing selections and options if not overridden by ->options
                            modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn (Builder $q) => $q->where('name', 'wakala'))
                        )
                        ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Chagua watumiaji wenye jukumu la "wakala".')
                        ->columnSpanFull()
                        ->visible(fn (string $operation) => $operation === 'edit'), // ONLY on edit
                ]),

                Forms\Components\Section::make('Vifaa vya Dukani (Simu za Miamala)')
                    ->schema([
                        // --- Field for CREATE page (devices_create_ids) ---
                         Select::make('devices_create_ids')
                            ->label('Sajili Vifaa Kwenye Duka Hili (Upangaji Mpya)')
                            ->options(
                                Device::query()
                                    ->whereNull('shop_id') // Example: Show only unassigned devices for selection
                                    ->get()
                                    ->mapWithKeys(fn (Device $device) => [$device->id => $device->device_id_display])
                                    ->all()
                            )
                            ->multiple()
                            ->searchable()
                            ->preload(false)
                            ->helperText('Chagua vifaa ambavyo havijapangiwa duka. Sajili kifaa kipya kwanza kama hakipo.')
                            ->columnSpanFull()
                            ->visible(fn (string $operation) => $operation === 'create'),

                        // --- Field for EDIT page (devices) ---
                        Select::make('devices') // This NAME must match the relationship method in Shop model
                            ->label('Vifaa Vilivyosajiliwa na Duka Hili')
                            ->relationship(
                                name: 'devices' // This uses the devices() HasMany relationship from Shop model
                                // NO titleAttribute here if using getOptionLabelFromRecordUsing extensively
                            )
                            ->getOptionLabelFromRecordUsing(fn (Device $record): string => $record->device_id_display) // <<< USE THIS
                            ->multiple()
                            ->preload()
                            ->searchable(['name', 'id']) // Allow searching actual columns on 'devices' table
                            ->helperText('Chagua vifaa vya kuongeza au kuondoa kwenye duka hili.')
                            ->columnSpanFull()
                            ->visible(fn (string $operation) => $operation === 'edit'),
                    ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Jina la Duka')->searchable()->sortable(),
                TextColumn::make('location')->label('Mahali'),

                Tables\Columns\ImageColumn::make('image_url') // Use the accessor
                  ->label('Picha')
                  ->disk('public') // Redundant if image_url is full URL, but good practice if it were just path
                  ->width(80)
                  ->height(60)
                  ->extraImgAttributes([
                      'class' => 'rounded-md border border-gray-300 shadow-sm',
                            ]),

                  TextColumn::make('fundingInvestment.investment_date') // Display date of funding investment
                      ->label('Uwekezaji Tarehe')
                      ->date('d M Y')
                      ->sortable(),


                TextColumn::make('initial_cash_on_hand')->label('Taslimu ya Kuanzia')->money('TZS')->sortable()->badge()->color('success'),
                TextColumn::make('mno_allocations_summary')
                    ->label('Floti ya Mitandao (Kuanzia)')
                    ->getStateUsing(function (Shop $record) {
                        if (empty($record->mno_initial_allocations)) return 'Hakuna taarifa';
                        return collect($record->mno_initial_allocations)
                            ->map(fn($alloc) => ucfirst($alloc['mno_key']) . ': ' . number_format($alloc['initial_float_allocated'] ?? 0) . ' TZS')
                            ->implode(', ');
                    }),
                TextColumn::make('assignedWakalas.name')
                    ->label('Mawakala Waliopewa')
                    ->badge()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->separator(', '),
                IconColumn::make('is_active')
                    ->label('Lipo Kazini?')
                    ->boolean(),
            ]);
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
