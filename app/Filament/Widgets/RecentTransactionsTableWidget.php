<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\AirtelTransaction; // Use this as the base model for the query context
use App\Models\HalotelTransaction; // Only needed for getTableRecords
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
// use Carbon\Carbon;

class RecentTransactionsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Miamala ya Hivi Karibuni';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 5;

    protected int $transactionsLimit = 10;

    /**
     * Define a base query.
     * This method is required by the TableWidget.
     * Even though getTableRecords() provides the final displayed dataset,
     * this query() method provides context for the table, such as the base model.
     */
    protected function query(): Builder // <<<< RENAMED FROM getTableQuery to query
    {
        // Provide a query from one of the transaction models.
        // This sets the "model context" for the table, even if getTableRecords
        // ultimately supplies a different set of data.
        // This helps Filament with things like action model binding.
        return AirtelTransaction::query()->latest()->limit(10);
    }

    public function getTableRecords(): EloquentCollection
    {
        $airtelRecent = AirtelTransaction::with(['type', 'user', 'customer'])
            ->latest('processed_at')
            ->take($this->transactionsLimit)
            ->get()
            ->map(function ($txn) {
                $txn->mno = 'Airtel';
                $txn->transaction_date = $txn->processed_at ?: $txn->created_at;
                return $txn;
            });

        $halotelRecent = HalotelTransaction::with(['type', 'user', 'customer'])
            ->latest('processed_at')
            ->take($this->transactionsLimit)
            ->get()
            ->map(function ($txn) {
                $txn->mno = 'Halotel';
                $txn->transaction_date = $txn->processed_at ?: $txn->created_at;
                return $txn;
            });

        $allRecentTransactions = $airtelRecent->concat($halotelRecent)
                                    ->sortByDesc('transaction_date')
                                    ->take(intval($this->transactionsLimit * 1.5));

        return new EloquentCollection($allRecentTransactions);
    }

    public function table(Table $table): Table
    {
        // Since getTableRecords() provides the records, we don't call $table->query() here.
        // The query() method defined above serves as the base for Filament.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tarehe/Muda')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mno')
                    ->label('MNO')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Airtel' => 'danger',
                        'Halotel' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Aina')
                    ->badge(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Mteja')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ref_no')
                    ->label('Namba Unukuzi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kiasi')
                    ->money('TZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission')
                    ->label('Kamisheni')
                    ->money('TZS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Wakala')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->defaultSort('transaction_date', 'desc')
            ->paginated(false); // Still good to have if getTableRecords fetches all intended display data
    }
}
