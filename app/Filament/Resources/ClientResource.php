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
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Str; 
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $modelLabel = 'klant';
    protected static ?int $navigationSort = 2;  
    protected static ?string $pluralModelLabel = 'klanten';

    // --- NAVIGATIE HERSTELD ---
    // Geen groups meer, gewoon standaard
    protected static ?string $navigationIcon = 'heroicon-o-users'; 

    // DE BADGE (Het getalletje 5)
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('organization_id', auth()->user()?->organization_id)->count();
    }
    // --------------------------

    public static function form(Form $form): Form
    {
        // Jouw bestaande logica voor de medische fiche
        $ficheFields = [];
        if (auth()->check()) {
            $definitions = \App\Models\FicheDefinition::where('organization_id', auth()->user()?->organization_id)
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
                                return $query->where('organization_id', auth()->user()?->organization_id);
                            })
                            ->live() 
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('house_id', $state);
                            })
                            ->required(),

                        \Filament\Forms\Components\Hidden::make('organization_id')
                            ->default(fn () => auth()->user()?->organization_id),

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
                        return $house && $house->planning_type !== 'day';
                    }),

                // SECTIE 3: MEDISCHE FICHE
                \Filament\Forms\Components\Section::make('Medische Fiche')
                    ->schema($ficheFields) 
                    ->columns(2),
                    
                // SECTIE 4: VOETEN ANALYSE
                \Filament\Forms\Components\Section::make('Voeten Analyse')
                    ->schema(function () {
                        return [
                            FeetDrawing::make('feet_drawing_path')
                                ->label('Voeten Analyse Tekening')
                                ->helperText('Teken met je vinger, stylus of muis de probleemzones direct op de afbeelding.')
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
                    ->relationship('house', 'name', fn($query) => $query->where('organization_id', auth()->user()?->organization_id)),
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
            'communication-sheet' => Pages\CommunicationSheet::route('/communication-sheet'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Planning')
                ->url(static::getUrl('planning'))
                ->icon('heroicon-o-calendar')
                ->group(static::getNavigationGroup())
                ->sort(3),
            NavigationItem::make('Communicatieblad')
                ->url(static::getUrl('communication-sheet'))
                ->icon('heroicon-o-document-text')
                ->group(static::getNavigationGroup())
                ->sort(4),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', auth()->user()?->organization_id);
    }
}