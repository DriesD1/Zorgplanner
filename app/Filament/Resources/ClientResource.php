<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Models\House; 
use App\Forms\Components\FeetDrawing; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; 
use Filament\Forms\Set; // Toegevoegd
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ClientResource extends Resource
{
    // Het Eloquent model dat deze resource gebruikt
    protected static ?string $model = Client::class;

    // Labels voor navigatie en weergave in het Filament admin panel
    protected static ?string $modelLabel = 'klant';
    protected static ?string $pluralModelLabel = 'klanten';
    protected static ?string $navigationLabel = 'Klanten';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * Definieert het formulier voor het aanmaken/bewerken van een klant.
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        // Dynamisch opbouwen van medische fiche velden op basis van FicheDefinition records van de ingelogde gebruiker
        $ficheFields = [];
        if (auth()->check()) {
            // Haal alle fiche-definities op voor de huidige gebruiker, gesorteerd op sort_order
            $definitions = \App\Models\FicheDefinition::where('user_id', auth()->id())
                ->orderBy('sort_order')
                ->get();

            // Maak voor elke definitie het juiste formuliercomponent aan
            foreach ($definitions as $def) {
                $fieldName = "fiche_data.{$def->id}";
                if ($def->type === 'checkbox') {
                    // Checkbox wordt weergegeven als een Toggle
                    $ficheFields[] = \Filament\Forms\Components\Toggle::make($fieldName)->label($def->label);
                } elseif ($def->type === 'text') {
                    // Tekstveld
                    $ficheFields[] = \Filament\Forms\Components\TextInput::make($fieldName)->label($def->label);
                }
            }
        }

        // Bouw het formulier op met verschillende secties
        return $form
            ->schema([
                // SECTIE 1: BASIS GEGEVENS
                // Persoonsgegevens van de klant
                \Filament\Forms\Components\Section::make('Persoonsgegevens')
                    ->schema([
                        // Selecteer het woonzorgcentrum/afdeling (alleen zichtbaar voor huizen van de huidige gebruiker)
                        \Filament\Forms\Components\Select::make('house_id')
                            ->label('Woonzorgcentrum / Afdeling')
                            ->relationship('house', 'name', function ($query) {
                                // Alleen huizen van de huidige gebruiker tonen
                                return $query->where('user_id', auth()->id());
                            })
                            ->live() // Zorgt dat het formulier direct ververst bij wijziging
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Zet direct de nieuwe waarde zodat afhankelijkheden updaten
                                $set('house_id', $state);
                            })
                            ->required(),

                        // Naam van de bewoner
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Volledige Naam')
                            ->required(),

                        // Kamernummer
                        \Filament\Forms\Components\TextInput::make('room_number')
                            ->label('Kamernummer'),
                    ])->columns(2),

                // SECTIE 2: PLANNING (De slimme sectie)
                // Alleen zichtbaar als het geselecteerde huis een custom schedule heeft
                \Filament\Forms\Components\Section::make('Planning Instellingen')
                    ->schema([
                        // Hoe vaak (in weken) moet de behandeling plaatsvinden
                        \Filament\Forms\Components\TextInput::make('frequency_weeks')
                            ->label('Frequentie (weken)')
                            ->numeric(),
                        
                        // Startdatum of volgende geplande behandeling
                        \Filament\Forms\Components\DatePicker::make('next_planned_date')
                            ->label('Startdatum / Volgende Behandeling')
                            ->native(false),
                    ])
                    ->columns(2)
                    ->visible(function (Get $get) {
                        // Alleen tonen als een huis geselecteerd is en dat huis een custom schedule heeft
                        $houseId = $get('house_id');
                        if (! $houseId) return false;
                        
                        $house = House::find($houseId);
                        // has_custom_schedule kan 1 of true zijn
                        return $house && ((bool) $house->has_custom_schedule === true);
                    }),

                // SECTIE 3: DE DYNAMISCHE MEDISCHE FICHE
                // Dynamisch gegenereerde velden op basis van de fiche-definities
                \Filament\Forms\Components\Section::make('Medische Fiche')
                    ->schema($ficheFields) 
                    ->columns(2),
                    
                // SECTIE 4: VOETEN ANALYSE
                // Speciale component voor het tekenen van probleemzones op een voet-afbeelding
                \Filament\Forms\Components\Section::make('Voeten Analyse')
                    ->schema([
                        FeetDrawing::make('feet_drawing_path')
                            ->label('Voeten Analyse Tekening')
                            ->helperText('Teken met je vinger, stylus of muis de probleemzones direct op de afbeelding.'),
                    ])
            ]);
    }

    /**
     * Definieert de tabelweergave van de klanten in het admin panel.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kamernummer kolom
                \Filament\Tables\Columns\TextColumn::make('room_number')->label('Kmr')->sortable()->searchable(),
                // Naam van de bewoner
                \Filament\Tables\Columns\TextColumn::make('name')->label('Naam Bewoner')->sortable()->searchable(),
                // Naam van het huis/afdeling
                \Filament\Tables\Columns\TextColumn::make('house.name')->label('Locatie / Afdeling')->sortable(),
                // Frequentie in weken
                \Filament\Tables\Columns\TextColumn::make('frequency_weeks')->label('Freq.')->suffix(' wkn')->toggleable(),
                // Volgende geplande bezoekdatum
                \Filament\Tables\Columns\TextColumn::make('next_planned_date')->label('Volgend Bezoek')->date('d/m/Y')->toggleable(),
            ])
            ->filters([
                // Filter op huis/afdeling, alleen huizen van de huidige gebruiker
                \Filament\Tables\Filters\SelectFilter::make('house_id')
                    ->label('Filter op Huis/Afdeling')
                    ->relationship('house', 'name', fn($query) => $query->where('user_id', auth()->id())),
            ])
            // Acties voor bewerken en bulk verwijderen
            ->actions([\Filament\Tables\Actions\EditAction::make()])
            ->bulkActions([\Filament\Tables\Actions\BulkActionGroup::make([\Filament\Tables\Actions\DeleteBulkAction::make()])]);
    }


    /**
     * Geeft de relation managers terug (geen in dit geval)
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Geeft de pagina's terug die bij deze resource horen (overzicht, aanmaken, bewerken, planning)
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'), // Overzichtspagina
            'create' => Pages\CreateClient::route('/create'), // Aanmaakpagina
            'edit' => Pages\EditClient::route('/{record}/edit'), // Bewerkpagina
            'planning' => Pages\Planning::route('/planning'), // Custom planning pagina
        ];
    }
}