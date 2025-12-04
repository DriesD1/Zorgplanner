<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FicheDefinitionResource\Pages;
use App\Filament\Resources\FicheDefinitionResource\RelationManagers;
use App\Models\FicheDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FicheDefinitionResource extends Resource
{
    protected static ?string $model = FicheDefinition::class;

    protected static ?string $modelLabel = 'fiche veld';
    protected static ?string $pluralModelLabel = 'fiche instellingen';
    protected static ?string $navigationLabel = 'Fiche Instellingen';
    protected static ?string $navigationIcon = 'heroicon-o-cog';

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            \Filament\Forms\Components\Section::make('Nieuw Veld Aanmaken')
                ->description('Maak een vraag die op elke klantenfiche moet komen.')
                ->schema([
                    // We slaan automatisch jouw ID op (zodat jij alleen jouw eigen vragen ziet)
                    \Filament\Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),

                    \Filament\Forms\Components\TextInput::make('label')
                        ->label('Naam van het veld')
                        ->placeholder('bv. Suikerziekte of Huisarts')
                        ->required(),

                    \Filament\Forms\Components\Select::make('type')
                        ->label('Soort veld')
                        ->options([
                            'text' => 'Tekstveld (om in te typen)',
                            'checkbox' => 'Ja/Nee Vinkje',
                            // Later kunnen we hier meer types toevoegen
                        ])
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('sort_order')
                        ->label('Volgorde')
                        ->numeric()
                        ->default(0)
                        ->helperText('Laag nummer staat bovenaan op de fiche.'),
                ])
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('label')
                ->label('Veldnaam')
                ->sortable()
                ->searchable(),
            \Filament\Tables\Columns\TextColumn::make('type')
                ->label('Type')
                ->badge() // Maakt er een mooi gekleurd labeltje van
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'text' => 'Tekst',
                    'checkbox' => 'Ja/Nee',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'text' => 'info', // Blauw
                    'checkbox' => 'success', // Groen
                    default => 'gray',
                }),
            \Filament\Tables\Columns\TextColumn::make('sort_order')
                ->label('Volgorde')
                ->sortable(),
        ])
        ->defaultSort('sort_order', 'asc') // Sorteer op volgorde
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFicheDefinitions::route('/'),
            'create' => Pages\CreateFicheDefinition::route('/create'),
            'edit' => Pages\EditFicheDefinition::route('/{record}/edit'),
        ];
    }
}
