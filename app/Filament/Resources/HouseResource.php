<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $modelLabel = 'huis';
    protected static ?string $pluralModelLabel = 'huizen';
    protected static ?string $navigationLabel = 'Huizen';
    protected static ?string $navigationIcon = 'heroicon-o-home';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Een sectie voor de basisgegevens
            \Filament\Forms\Components\Section::make('Locatie Gegevens')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Naam Woonzorgcentrum')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('address')
                        ->label('Adres'),
                ])->columns(2),

            // Een sectie voor communicatie
            \Filament\Forms\Components\Section::make('Communicatie & Planning')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('contact_email')
                        ->label('Email Hoofdverpleging')
                        ->email()
                        ->helperText('Hier wordt het weekrapport naartoe gestuurd.'),
                    
                    \Filament\Forms\Components\Toggle::make('has_custom_schedule')
                        ->label('Beheer ik zelf de planning?')
                        ->onColor('success')
                        ->offColor('gray')
                        ->helperText('AAN: Ik bepaal wanneer ik ga (Matrix). UIT: Zij geven mij een lijst.'),
                ])->columns(2),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('name')
                ->label('Naam')
                ->searchable(),
            \Filament\Tables\Columns\TextColumn::make('contact_email')
                ->label('Email')
                ->icon('heroicon-m-envelope'),
            \Filament\Tables\Columns\IconColumn::make('has_custom_schedule')
                ->label('Eigen Planning')
                ->boolean(),
        ])
        ->filters([
            //
        ])
        ->actions([
            \Filament\Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }
}
