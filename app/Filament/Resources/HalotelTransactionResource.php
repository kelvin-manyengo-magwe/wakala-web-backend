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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;



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

                TextColumn::make('type.name')
                        ->label('Aina')
                        ->badge() // This will put the text in a nice-looking colored badge
                        ->formatStateUsing(function (string $state): string {
                            // This function checks the value from the database and returns the Swahili equivalent.
                            if (strtolower($state) === 'deposit') {
                                return 'Kuweka'; // "Deposit" becomes "Weka"
                            }
                            if (strtolower($state) === 'withdrawal') {
                                return 'Kutoa'; // "Withdrawal" becomes "Toa"
                            }
                            // As a fallback, just return the original value capitalized.
                            return ucfirst($state);
                        })
                        ->color(fn (string $state): string => match (strtolower($state)) {
                            // This function sets the color of the badge based on the type.
                            'deposit' => 'success', // "Weka" will be green
                            'withdrawal' => 'danger',   // "Toa" will be red
                            default => 'gray',          // Any other type will be gray
                        }),


                TextColumn::make('amount')->money('TZS')->sortable()->label('Kiasi'),
                TextColumn::make('commission')->money('TZS')->sortable()->label('Kamisheni'),
                TextColumn::make('user.name')->label('Wakala')->searchable(),

                TextColumn::make('shop.name')->label('Duka')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('float_balance')->label('Float Baada ya Muamala')->money('TZS')->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('processed_at')->dateTime()->sortable()->label('Ilisindikwa')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Iliingizwa Mfumo')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                    SelectFilter::make('shop_id')
                      ->label('Chuja kwa Duka')
                      ->relationship('shop', 'name')
                      ->searchable()->preload(),
                  SelectFilter::make('user_id')
                      ->label('Chuja kwa Wakala Aliyeingiza')
                      ->options(
                                User::whereHas('roles', function ($query) {
                                    $query->where('name', 'wakala');
                                })->pluck('name', 'id')
                            )
                      ->searchable()->preload(),
                  Tables\Filters\Filter::make('processed_at')
                      ->form([Forms\Components\DatePicker::make('tarehe_kuanzia')->label('Kuanzia Tarehe'), Forms\Components\DatePicker::make('tarehe_kumaliza')->label('Hadi Tarehe'), ])
                      ->query(function (Builder $query, array $data): Builder {
                          return $query
                              ->when($data['tarehe_kuanzia'], fn (Builder $query, $date): Builder => $query->whereDate('processed_at', '>=', $date))
                              ->when($data['tarehe_kumaliza'], fn (Builder $query, $date): Builder => $query->whereDate('processed_at', '<=', $date));
                      }),


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
