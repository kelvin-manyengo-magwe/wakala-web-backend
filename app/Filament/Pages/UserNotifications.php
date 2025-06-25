<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

// You need to import the Notification model to use it in the query
use Illuminate\Notifications\DatabaseNotification;

class UserNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $slug = 'taarifa-zangu';
    protected static ?string $title = 'Taarifa Zako';
    protected static string $view = 'filament.pages.user-notifications';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        // This marks notifications as read after the initial data has been counted for the badge
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function table(Table $table): Table
    {
        return $table
            // ##### THIS IS THE CORRECT AND FINAL FIX #####
            // 1. We start the query from the Notification model itself.
            // 2. We use a `where` clause to filter for the currently logged-in user.
            // This returns the correct `Builder` object that Filament's table requires.
            ->query(DatabaseNotification::query()->where('notifiable_id', Auth::id()))

            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('data.title')
                    ->label('Kichwa cha Habari')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('data.body')
                    ->label('Ujumbe')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Muda')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('delete')
                        ->label('Futa')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->delete())
                ])
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Futa Zilizochaguliwa')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->delete())
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ])
            ->headerActions([
                Action::make('deleteAll')
                    ->label('Futa Zote')
                    ->color('danger')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->requiresConfirmation()
                    ->action(fn () => Auth::user()->notifications()->delete()),
            ]);
    }
}
