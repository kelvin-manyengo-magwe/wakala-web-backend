<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
// use App\Filament\Resources\RoleResource\RelationManagers; // Commented out as we're not managing permissions here
use Spatie\Permission\Models\Role; // Make sure this is your Role model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class; // Correctly pointing to Spatie's Role model

    protected static ?string $navigationIcon = 'heroicon-o-user-group'; // Icon suggesting roles or groups of users
    protected static ?int $navigationSort = 3; // Adjust as needed

    // --- Swahili Labels ---
    public static function getNavigationGroup(): ?string
    {
        return null; // "Administration"
    }

    public static function getNavigationLabel(): string
    {
        return 'Majukumu ya Watumiaji'; // "User Roles" - more descriptive if permissions are separate
    }

    public static function getModelLabel(): string // Singular
    {
        return 'Jukumu la Mtumiaji'; // "User Role"
    }

    public static function getPluralModelLabel(): string // Plural
    {
        return 'Majukumu ya Watumiaji'; // "User Roles"
    }
    // --- End Swahili Labels ---


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Jina la Jukumu') // "Role Name"
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true, table: 'roles') // Ensure uniqueness in the 'roles' table
                    ->helperText('Weka jina la kipekee la jukumu, k.m., "Msimamizi", "Mhariri", "Mteja".'), // "Enter a unique role name..."

                // The 'guard_name' is often important for Spatie permissions,
                // but typically defaults to 'web'. If you need to set it explicitly:
                // Forms\Components\TextInput::make('guard_name')
                //     ->label('Jina la Mlinzi') // "Guard Name"
                //     ->default('web') // Default guard
                //     ->required()
                //     ->helperText('Kwa kawaida hii itakuwa "web" kwa watumiaji wa kawaida wa tovuti.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              Tables\Columns\TextColumn::make('name')
                ->label('Jina la Jukumu') // "Role Name"
                ->searchable()
                ->sortable(),

              // If you still want to show a count of users with this role (optional)
              // Tables\Columns\TextColumn::make('users_count')
              //   ->counts('users') // Assumes your Role model has a 'users' relationship
              //   ->label('Idadi ya Watumiaji'), // "Number of Users"

              Tables\Columns\TextColumn::make('created_at')
                ->label('Tarehe ya Kuundwa') // "Date Created"
                ->dateTime('d M Y, H:i') // Swahili friendly date format
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true), // Make it toggleable
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Hariri'),   // "Edit"
                Tables\Actions\DeleteAction::make()->label('Futa'),  // "Delete" - consider implications before enabling
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Futa Zilizochaguliwa'), // "Delete Selected"
                ]),
            ])
            ->defaultSort('name', 'asc'); // Sort by name by default
    }

    public static function getRelations(): array
    {
        return [
            // No relation managers here if we are not managing permissions
            // RelationManagers\PermissionsRelationManager::class, // REMOVED
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
            // 'view' => Pages\ViewRole::route('/{record}'), // View page might be less useful if only showing name
        ];
    }

    /**
     * If you are using Spatie Permission and want to restrict access to this resource.
     * Example: Only users with 'manage roles' permission can access.
     */
    // public static function canViewAny(): bool
    // {
    //    return auth()->user()->can('view roles');
    // }
}
