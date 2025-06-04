<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HalotelTransactionResource\Pages;
use App\Models\HalotelTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class HalotelTransactionResource extends Resource
{
    protected static ?string $model = HalotelTransaction::class;
    protected static ?string $navigationIcon = ''; // Chagua ikoni inayofaa
    protected static ?int $navigationSort = 11;

    public static function getNavigationIcon(): string | HtmlString | null
    {
        $logoUrl = asset('images/mno/halo-pesa-logo.png'); // Adjust path & filename
        return new HtmlString('<img src="' . $logoUrl . '" alt="Halotel Icon" class="w-5 h-5 object-contain rtl:ml-2">');
    }

    public static function getNavigationGroup(): ?string
    {
        //return __('nav_groups.transactions'); // Itakuwa 'Miamala'
        return 'Miamala';
        // AU kwa kuweka moja kwa moja:
        // return 'Miamala';
    }

    public static function getNavigationLabel(): string
    {
        return 'Miamala ya Halotel';
    }

    public static function getModelLabel(): string
    {
        return 'Muamala wa Halotel';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Miamala ya Halotel';
    }

    public static function form(Form $form): Form
    {
        // Schema sawa na ya Airtel kwa sasa, badilisha ikihitajika
        return $form
            ->schema([
                Forms\Components\TextInput::make('ref_no')
                    ->label('Namba ya Unukuzi')
                    ->disabledOn('view')
                    ->required(),
                Forms\Components\DateTimePicker::make('date')
                    ->label('Tarehe')
                    ->disabledOn('view')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Kiasi')
                    ->numeric()
                    ->prefix('Tsh')
                    ->disabledOn('view')
                    ->required(),
                Forms\Components\TextInput::make('commission')
                    ->label('Kamisheni')
                    ->numeric()
                    ->prefix('Tsh')
                    ->disabledOn('view'),
                Forms\Components\TextInput::make('float_balance')
                    ->label('Salio la Float')
                    ->numeric()
                    ->prefix('Tsh')
                    ->disabledOn('view'),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Wakala')
                    ->disabledOn('view'),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Jina la Mteja')
                    ->searchable()
                    ->preload()
                    ->disabledOn('view'),
                Forms\Components\Select::make('type_id')
                    ->relationship('type', 'name')
                    ->label('Aina ya Muamala')
                    ->disabledOn('view'),
                Forms\Components\DateTimePicker::make('processed_at')
                    ->label('Ilisindikwa Lini')
                    ->disabledOn('view'),
                Forms\Components\Textarea::make('raw_payload')
                    ->label('Data Ghafi')
                    ->columnSpanFull()
                    ->disabledOn('view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Schema sawa na ya Airtel, badilisha ikihitajika
        return $table
            ->columns([
                TextColumn::make('date')->dateTime()->sortable()->label('Tarehe'),
                TextColumn::make('ref_no')->searchable()->label('Namba Unukuzi'),
                TextColumn::make('customer.name')->searchable()->label('Mteja'),
                TextColumn::make('type.name')->label('Aina'),
                TextColumn::make('amount')->money('TZS')->sortable()->label('Kiasi'),
                TextColumn::make('commission')->money('TZS')->sortable()->label('Kamisheni'),
                TextColumn::make('user.name')->label('Wakala')->searchable(),
                TextColumn::make('processed_at')->dateTime()->sortable()->label('Ilisindikwa')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Iliingizwa Mfumo')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Vichujio
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Bulk actions
            ])
            ->defaultSort('processed_at', 'desc')
            ->poll('15s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHalotelTransactions::route('/'),
        ];
    }
}
