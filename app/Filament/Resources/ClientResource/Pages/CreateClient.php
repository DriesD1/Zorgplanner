<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    // 1. Dit is de standaard redirect (voor de enkele "Aanmaken" knop)
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    // 2. Hier lossen we het canvas-probleem op
    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            // De 'after' hook wordt uitgevoerd NADAT de klant succesvol is opgeslagen
            ->after(fn () => $this->js('window.location.reload()'));
    }
}