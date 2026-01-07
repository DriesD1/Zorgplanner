<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; // <--- Belangrijk voor de logica
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $modelLabel = 'huis';
    protected static ?int $navigationSort = 1;  
    protected static ?string $pluralModelLabel = 'huizen';
    protected static ?string $navigationLabel = 'Huizen';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('organization_id', auth()->user()?->organization_id)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Een sectie voor de basisgegevens
                Forms\Components\Section::make('Locatie Gegevens')
                    ->schema([
                        Forms\Components\Hidden::make('organization_id')
                            ->default(fn () => auth()->user()?->organization_id),

                        Forms\Components\TextInput::make('name')
                            ->label('Naam Woonzorgcentrum')
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Adres'),
                    ])->columns(2),

                // Een sectie voor communicatie & Planning
                Forms\Components\Section::make('Communicatie & Planning')
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email Hoofdverpleging')
                            ->email()
                            ->helperText('Hier wordt het weekrapport naartoe gestuurd.')
                            ->columnSpanFull(),
                        
                        // DE SCHAKELAAR
                        Forms\Components\Toggle::make('has_custom_schedule')
                            ->label('Beheer ik zelf de planning?')
                            ->helperText('AAN: Ik bepaal wanneer ik ga. UIT: Zij geven mij een lijst.')
                            ->onColor('success')
                            ->offColor('gray')
                            ->live(), // <--- Zorgt dat het formulier direct reageert
                        
                        // HET KEUZE MENU (Verschijnt alleen als schakelaar aan staat)
                        Forms\Components\Select::make('planning_type')
                            ->label('Hoe wordt er gepland?')
                            ->options([
                                'week' => 'Weekmatrix (Om de zoveel weken)',
                                'day' => 'Dagagenda (Specifieke datum & tijd)',
                            ])
                            ->default('week')
                            ->required()
                            // Toon dit veld ALLEEN als has_custom_schedule op TRUE staat
                            ->visible(fn (Get $get) => $get('has_custom_schedule') === true),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope'),
                
                // Laat zien of eigen planning aan staat
                Tables\Columns\IconColumn::make('has_custom_schedule')
                    ->label('Eigen Planning')
                    ->boolean(),

                // Laat zien welk TYPE planning het is (week of dag)
                Tables\Columns\TextColumn::make('planning_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'week' => 'Weekmatrix',
                        'day' => 'Dagagenda',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'week' => 'info',
                        'day' => 'warning',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', auth()->user()?->organization_id);
    }
}