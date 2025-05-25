<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {


        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                //Forms\Components\DateTimePicker::make('email_verified_at'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->helperText('This password will be sent via SMS to wakala')
                    ->maxLength(255),


                    Forms\Components\TextInput::make('phone_no')
                      ->label('Phone Number')
                      ->prefix('+255')
                      ->tel()
                      ->required()
                      ->unique(ignoreRecord: true)
                      ->mask('999 999 999')
                      ->placeholder('712 345 678')
                      ->afterStateUpdated(function ($state, $set) {
                          $digits = substr(preg_replace('/[^0-9]/', '', $state), 0, 9);
                          $set('phone_no', $digits);
                      })
                      ->dehydrateStateUsing(function ($state) {
                          $digits = substr(preg_replace('/[^0-9]/', '', $state), 0, 9);
                          return '+255' . $digits;
                      })
                      ->rules([
                          function () {
                              return function (string $attribute, $value, \Closure $fail) {
                                  $digits = preg_replace('/[^0-9]/', '', $value);

                                  if (strlen($digits) !== 9) {
                                      $fail('Phone number must have exactly 9 digits after +255');
                                  }
                              };
                          }
                      ]),

                    Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->visible(fn() => auth()->user()->hasRole('admin')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_no')
                    ->label('Phone Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                  //  ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    //->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
