<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions; // <--- Zorg dat deze import er staat
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public string $feetDrawingKey;

    public function mount(): void
    {
        parent::mount();
        // Nieuw canvas key zodat het component volledig her-rendert
        $this->feetDrawingKey = (string) Str::uuid();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Reset canvas in UI en forceer een nieuwe component key
        $this->dispatch('reset-drawing');
        $this->feetDrawingKey = (string) Str::uuid();
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            // Zodra het opslaan gelukt is, sturen we een event 'reset-drawing'
            ->after(fn () => $this->dispatch('reset-drawing'));
    }
}