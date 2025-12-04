<?php

namespace App\Filament\Resources\FicheDefinitionResource\Pages;

use App\Filament\Resources\FicheDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFicheDefinitions extends ListRecords
{
    protected static string $resource = FicheDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
