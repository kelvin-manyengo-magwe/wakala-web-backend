<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
// use App\Models\TransactionType; // Keep if used in future modifications
// use App\Models\User;           // Keep if used in future modifications
// use Illuminate\Database\Eloquent\Builder; // Keep if used in future modifications

// Import the correct Collection type
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // <<<< CHANGE/ADD THIS
use Illuminate\Support\Collection; // This can be removed if not used for other things

// use Carbon\Carbon; // Keep if used

class RecentTransactionsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Miamala ya Hivi Karibuni';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 5;

    protected int $transactionsLimit = 10;

    /**
     * Changed return type to EloquentCollection
     */
    public function getTableRecords(): EloquentCollection // <<<< CHANGE THIS RETURN TYPE
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

        // Merge and sort the collections. The result of concat and sortByDesc on Eloquent collections
        // is typically still an Eloquent Collection if all items are Eloquent models.
        // If it degrades to a base Support Collection, we might need to cast or wrap.
        $allRecentTransactions = $airtelRecent->concat($halotelRecent)
                                    ->sortByDesc('transaction_date')
                                    ->take($this->transactionsLimit * 1.5); // Taking slightly more for variety
                                    // ->values(); // Ensure keys are reset for collection methods if needed

        // Ensure the final result is an EloquentCollection
        // If $allRecentTransactions is a base Support\Collection, convert it.
        // However, Eloquent's concat/sortByDesc usually preserve the EloquentCollection type
        // if the underlying items are Eloquent models.
        // If you face issues where it becomes a base Collection, you can do:
        // return new EloquentCollection($allRecentTransactions->all());

        // Let's assume $allRecentTransactions is already an EloquentCollection because its items are models
        // If it is actually a base Support\Collection due to merging of different model types (though here they are mapped to similar structure),
        // a simple cast like below might not be enough.
        // For now, directly returning is usually fine if the contents are homogeneous enough.
        return new EloquentCollection($allRecentTransactions); // Explicitly cast to EloquentCollection
    }

    public function table(Table $table): Table
    {
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
            ->defaultSort('transaction_date', 'desc');
    }
}
