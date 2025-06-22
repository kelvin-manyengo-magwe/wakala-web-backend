<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User; // Your User model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString; // Needed for custom HTML in Select options
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    // --- Swahili Navigation & Model Labels ---
    public static function getNavigationGroup(): ?string
    {
        // return 'Usimamizi wa Akaunti'; // Example: "Account Management"
        return 'Utawala'; // Make it a top-level item for simplicity
    }

    public static function getNavigationLabel(): string
    {
        return 'Mawakala'; // "Agents (Users)"
    }

    public static function getModelLabel(): string // Singular
    {
        return 'Wakala'; // "Agent"
    }

    public static function getPluralModelLabel(): string // Plural
    {
        return 'Wakala'; // "Agents"
    }
    // --- End Swahili Labels ---

    public static function form(Form $form): Form
    {
        // MNO Definitions: key => [Swahili Label, Logo Path (relative to public)]
        $mnoDefinitions = [
            'airtel'  => ['Airtel Money', 'images/mno/airtel-money-logo.png'],
            'halotel' => ['Halopesa', 'images/mno/halo-pesa.png'],
            'tigo'    => ['Tigo Pesa', 'images/mno/mixx-by-yas-logo.png'],
            'mpesa'   => ['M-Pesa', 'images/mno/mpesa-logo.jpg'],
            // Add others if needed
        ];

        // Prepare options array for the Select component
        $mnoSelectOptions = collect($mnoDefinitions)->mapWithKeys(fn($details, $key) => [$key => $details[0]])->toArray();

        // Renderer function for select options with logos
        $mnoOptionRenderer = function (string $value) use ($mnoDefinitions): HtmlString {
            $details = $mnoDefinitions[$value] ?? null;
            if (!$details) {
                return new HtmlString("<span>{$value}</span>"); // Fallback if key not found
            }
            return new HtmlString(
                view('filament.forms.components.mno-select', [
                    'value' => $value,
                    'label' => $details[0],         // Swahili Label
                    'logoUrl' => asset($details[1]) // Full URL to logo
                ])->render()
            );
        };

        return $form
            ->schema([
                Forms\Components\Section::make('Taarifa za Msingi za Wakala') // "Agent's Basic Information"
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Jina Kamili') // "Full Name"
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Barua Pepe') // "Email"
                            ->email()
                            ->nullable()
                            ->helperText('Barua pepe ya wakala, si lazima')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_no')
                            ->label('Namba ya Simu Kuu') // "Main Phone Number"
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Namba ya simu ambayo wakala atatumia kwa mawasiliano.'), // "Phone number agent will use for communication"
                        TextInput::make('location')
                            ->label('Eneo analopatikana') // "Location where agent is found"
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('password')
                            ->label('Nenosiri') // "Password"
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Wakati wa kuhariri, acha wazi ili kubaki na nenosiri la zamani.'), // "On edit, leave blank to keep old password."
                    ]),

                Forms\Components\Section::make('Namba za Till za Wakala') // "Agent's Till Numbers"
                    ->collapsible()
                    ->schema([
                        Repeater::make('till_no') // Attribute name matches DB JSON column
                            ->label('') // Repeater itself has no label, section has one
                            ->addActionLabel('Ongeza Namba ya Till') // "Add Till Number"
                            ->columns(2) // MNO Select and Till Input side-by-side
                            ->minItems(0) // Agent can have zero till numbers initially
                            ->defaultItems(1) // Start with one till item when adding a new User
                            ->itemLabel(function (array $state): ?string {
                                $mnoKey = $state['mno_key'] ?? null;
                                $tillNumber = $state['till_no'] ?? null;
                                $mnoName = $mnoKey ? ($mnoDefinitions[$mnoKey][0] ?? ucfirst($mnoKey)) : 'Mtandao Usiojulikana';
                                return $tillNumber ? "{$mnoName}: {$tillNumber}" : null;
                            })
                            ->schema([
                                Select::make('mno_key') // Key for the MNO, stored in JSON
                                    ->label('Mtandao wa Simu (MNO)') // "Mobile Network (MNO)"
                                    ->options($mnoSelectOptions) // e.g., ['airtel' => 'Airtel Money']
                                    ->allowHtml() // Crucial for rendering custom HTML options
                                    ->getOptionLabelUsing($mnoOptionRenderer) // Uses custom blade component for display
                                    // ->getSearchResultsUsing( ... ) // For more advanced search with images
                                    ->searchable()
                                    ->live() // Update itemLabel when MNO changes
                                    ->required(),
                                TextInput::make('till_no') // Till number, stored in JSON
                                    ->label('Namba ya Till') // "Till Number"
                                    ->tel() // For numeric input with tel patterns
                                    ->required()
                                    ->live(onBlur: true) // Update itemLabel when till_no changes
                                    ->maxLength(50),
                            ])
                            ->columnSpanFull(), // Repeater takes full width of this section
                    ]),


                    Forms\Components\Section::make('Maduka Anayohudumia Wakala Huyu') // "Shops this Agent Serves"
                    ->description('Chagua duka moja au zaidi ambapo wakala huyu atafanya kazi.')
                    ->collapsible()
                    ->schema([
                        Select::make('assignedShops') // Name matches the relationship in User model
                            ->label('Chagua Duka (au Maduka)') // "Select Shop (or Shops)"
                            ->relationship(
                                name: 'assignedShops', // The BelongsToMany relationship method name on User model
                                titleAttribute: 'name'   // Display the 'name' of the Shop
                            )
                            ->multiple() // Allow selecting multiple shops
                            ->preload()  // Load existing shops
                            ->searchable()
                            ->helperText('Kama wakala atahudumu kwenye duka maalumu, lichague hapa.')
                            ->columnSpanFull(),
                    ]),


                          Select::make('roles')
                          ->label('Majukumu')
                          ->relationship('roles', 'name')
                          ->multiple()
                          ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // MNO Definitions: key => [Swahili Label, Logo Path] - also needed for table display if showing logos
        $mnoDefinitions = [
            'airtel'  => ['Airtel Money', asset('images/mno/airtel-money-logo.png')],
            'halotel' => ['Halopesa', asset('images/mno/halo-pesa-logo.png')],
            'tigo'    => ['Tigo Pesa', asset('images/mno/mixx-by-yas-logo.png')],
            'mpesa'   => ['M-Pesa', asset('images/mno/mpesa-logo.jpg')],
        ];

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->formatStateUsing(function ($state) {
                      return '<span style="font-size: 1.25rem;">🧑‍💼</span> ' . e($state);
                  })->html()
                    ->label('Jina Kamili')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Barua Pepe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_no')
                    ->label('Namba ya Simu')
                    ->searchable(),

                    // Displaying Shops assigned to this Wakala
                Tables\Columns\TextColumn::make('assignedShops.name') // Assumes 'assignedShops' is relationship in User model
                        ->label('Maduka Anayohudumu') // "Shops They Serve At"
                        ->badge()
                        ->color('warning')
                        ->limitList(2)->expandableLimitedList()
                        ->separator(', '),


                Tables\Columns\TextColumn::make('location')
                    ->label('Eneo')
                    ->toggleable(isToggledHiddenByDefault: true),

                    // Column for Roles
                Tables\Columns\TextColumn::make('roles.name') // Assumes 'roles' is the relationship name
                          ->label('Majukumu') // "Roles"
                          ->badge() // Display roles as badges
                          ->separator(',') // If a user has multiple roles, separate them by comma

                          ->sortable(),

                // Custom column to display Till Numbers
                Tables\Columns\ViewColumn::make('till_no')
                    ->label('Namba za Till')
                    ->view('tables.columns.user-till-numbers'), // Custom blade view for this column
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarehe ya Kujiunga')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Angalia'),
                Tables\Actions\EditAction::make()->label('Hariri'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Futa Vilivyochaguliwa'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('wakala'); // Filter by 'wakala' role
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'), // Ensure create page exists and path is right
            'edit' => Pages\EditUser::route('/{record}/edit'),
          //  'view' => Pages\ViewUser::route('/{record}'),     // Ensure view page exists
        ];
    }
}
