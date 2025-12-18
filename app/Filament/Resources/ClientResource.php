<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\House; 
use App\Forms\Components\FeetDrawing; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; 
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str; 

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $modelLabel = 'klant';
    protected static ?string $pluralModelLabel = 'klanten';
    protected static ?string $navigationLabel = 'Klanten';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        // Dynamisch opbouwen van medische fiche velden
        $ficheFields = [];
        if (auth()->check()) {
            $definitions = \App\Models\FicheDefinition::where('user_id', auth()->id())
                ->orderBy('sort_order')
                ->get();

            foreach ($definitions as $def) {
                $fieldName = "fiche_data.{$def->id}";
                if ($def->type === 'checkbox') {
                    $ficheFields[] = \Filament\Forms\Components\Toggle::make($fieldName)->label($def->label);
                } elseif ($def->type === 'text') {
                    $ficheFields[] = \Filament\Forms\Components\TextInput::make($fieldName)->label($def->label);
                }
            }
        }

        return $form
            ->schema([
                // SECTIE 1: BASIS GEGEVENS
                \Filament\Forms\Components\Section::make('Persoonsgegevens')
                    ->schema([
                        \Filament\Forms\Components\Select::make('house_id')
                            ->label('Woonzorgcentrum / Afdeling')
                            ->relationship('house', 'name', function ($query) {
                                return $query->where('user_id', auth()->id());
                            })
                            ->live() 
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('house_id', $state);
                            })
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Volledige Naam')
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('room_number')
                            ->label('Kamernummer'),
                    ])->columns(2),

                // SECTIE 2: PLANNING INSTELLINGEN
                \Filament\Forms\Components\Section::make('Planning Instellingen')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('frequency_weeks')
                            ->label('Frequentie (weken)')
                            ->numeric()
                            ->minValue(1),
                        
                        \Filament\Forms\Components\DatePicker::make('next_planned_date')
                            ->label('Startdatum / Volgende Behandeling')
                            ->native(false),
                    ])
                    ->columns(2)
                    ->visible(function (Get $get) {
                        $houseId = $get('house_id');
                        if (! $houseId) return false;
                        
                        $house = House::find($houseId);
                        
                        // Verberg als het dagplanning is
                        return $house && $house->planning_type !== 'day';
                    }),

                // SECTIE 3: DE DYNAMISCHE MEDISCHE FICHE
                \Filament\Forms\Components\Section::make('Medische Fiche')
                    ->schema($ficheFields) 
                    ->columns(2),
                    
                // SECTIE 4: VOETEN ANALYSE
                \Filament\Forms\Components\Section::make('Voeten Analyse')
                    ->schema(function () {
                        // DE FIX: Door een Closure (functie) te gebruiken voor het schema,
                        // wordt Str::random() elke keer opnieuw uitgevoerd als het formulier laadt.
                        // Dit garandeert een unieke key, ook bij "Create & Create Another".
                        return [
                            FeetDrawing::make('feet_drawing_path')
                                ->label('Voeten Analyse Tekening')
                                ->helperText('Teken met je vinger, stylus of muis de probleemzones direct op de afbeelding.')
                                // Gebruik een Livewire-key zodat "Aanmaken & nieuwe aanmaken" een verse canvas toont.
                                ->key(fn ($livewire) => $livewire->feetDrawingKey ?? Str::random()), 
                        ];
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('room_number')->label('Kmr')->sortable()->searchable(),
                \Filament\Tables\Columns\TextColumn::make('name')->label('Naam Bewoner')->sortable()->searchable(),
                \Filament\Tables\Columns\TextColumn::make('house.name')->label('Locatie / Afdeling')->sortable(),
                \Filament\Tables\Columns\TextColumn::make('frequency_weeks')->label('Freq.')->suffix(' wkn')->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('next_planned_date')->label('Volgend Bezoek')->date('d/m/Y')->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('house_id')
                    ->label('Filter op Huis/Afdeling')
                    ->relationship('house', 'name', fn($query) => $query->where('user_id', auth()->id())),
            ])
            ->actions([\Filament\Tables\Actions\EditAction::make()])
            ->bulkActions([\Filament\Tables\Actions\BulkActionGroup::make([\Filament\Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
            'planning' => Pages\Planning::route('/planning'),
        ];
    }
}