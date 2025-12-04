<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Knop 1: De standaard aanmaak knop
            Actions\CreateAction::make()
                ->label('Klant aanmaken'),

            // Knop 2: JOUW NIEUWE PLANNING KNOP
            Actions\Action::make('planning')
                ->label('Planning')
                ->icon('heroicon-o-calendar')
                ->color('info') // Blauwe knop
                ->url(ClientResource::getUrl('planning')), // Dit is de link!
        ];
    }
}