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
use Filament\Forms\Components\TextInput;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {


        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Jina')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Barua Pepe')
                    ->email()
                    //->required()
                    ->maxLength(255),
                //Forms\Components\DateTimePicker::make('email_verified_at'),

                Forms\Components\TextInput::make('password')
                    ->label('Neno Siri')
                    ->password()
                    ->required()
                    ->helperText('Nenosiri hili litatumwa kwa SMS kwa wakala')
                    ->maxLength(255),


                    Forms\Components\TextInput::make('phone_no')
                      ->label('Nambari ya Simu')
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
                                      $fail('Nambari ya simu lazima iwe na tarakimu 9 baada ya +255');
                                  }
                              };
                          }
                      ]),

                    TextInput::make('location')
                        ->label(__('Mahali')),

                    TextInput::make('till_no')
                        ->label(__('Namba ya Till'))
                        ->unique(ignoreRecord: true),

                    Select::make('roles')
                            ->label('Majukumu')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            /*->visible(function() {   //disabled the admin role cause web is default admin role
                                          \Log::debug('User has admin role? '. (auth()->user()->hasRole('admin') ? 'Yes' : 'No'));
                                          return auth()->user()->hasRole('admin');
                                      })*/,
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
                Tables\Columns\TextColumn::make('location')
                    ->label(__('Location'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('till_no')
                    ->label(__('till_no'))
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

    public static function getNavigationLabel(): string
      {
          return __('Mawakala');
      }

    public static function getModelLabel(): string
        {
            return __('Wakala');
        }

    public static function getPluralModelLabel(): string
        {
            return __('Mawakala');
        }

}
