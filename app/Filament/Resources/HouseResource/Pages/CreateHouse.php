<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHouse extends CreateRecord
{
    protected static string $resource = HouseResource::class;

    // --- VOEG DEZE FUNCTIE TOE ---
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id(); // Dit koppelt het huis aan JOU (de ingelogde gebruiker)
    
        return $data;
    }
    // -----------------------------
}