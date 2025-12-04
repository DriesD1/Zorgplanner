<?php

namespace App\Filament\Resources\FicheDefinitionResource\Pages;

use App\Filament\Resources\FicheDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFicheDefinition extends CreateRecord
{
    protected static string $resource = FicheDefinitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id(); // Dit koppelt het huis aan JOU (de ingelogde gebruiker)
    
        return $data;
    }
}