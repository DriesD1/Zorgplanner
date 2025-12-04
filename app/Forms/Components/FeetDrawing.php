<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeetDrawing extends Field
{
    // app/Forms/Components/FeetDrawing.php
    protected string $view = 'forms.components.feet-drawing'; 
        
    // Dit zorgt ervoor dat de getekende data (Base64) naar de database wordt gestuurd
    protected function setUp(): void
    {
        parent::setUp();

        // De key in de form data is 'feet_drawing_path'
        $this->statePath('feet_drawing_path'); 

        // We stellen een hoogte in voor de canvas
        $this->extraAttributes([
            'style' => 'height: 400px; max-width: 600px;',
        ]);

        // Converteer base64 naar bestandspad bij het opslaan
        $this->dehydrateStateUsing(function ($state, $record) {
            if (!$state) {
                return null;
            }

            // Als het al een pad is (geen data:image prefix), gewoon retourneren
            if (!str_starts_with($state, 'data:image/')) {
                return $state;
            }

            // Base64 image naar bestand converteren
            $image = $state;
            
            // Verwijder oude afbeelding als die bestaat
            if ($record && $record->feet_drawing_path && Storage::disk('public')->exists($record->feet_drawing_path)) {
                Storage::disk('public')->delete($record->feet_drawing_path);
            }

            // Extract image data
            preg_match('/^data:image\/(\w+);base64,/', $image, $matches);
            $imageType = $matches[1] ?? 'png';
            $imageData = substr($image, strpos($image, ',') + 1);
            $imageData = base64_decode($imageData);

            // Genereer unieke bestandsnaam
            $filename = 'feet_drawings/' . Str::uuid() . '.' . $imageType;
            
            // Opslaan in storage/app/public
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        });

        // Converteer bestandspad terug naar base64 bij het laden
        $this->afterStateHydrated(function ($component, $state) {
            if (!$state) {
                return;
            }

            // Als het al base64 data is, gewoon retourneren
            if (str_starts_with($state, 'data:image/')) {
                return;
            }

            // Laad bestand en converteer naar base64
            if (Storage::disk('public')->exists($state)) {
                $imageData = Storage::disk('public')->get($state);
                $extension = pathinfo($state, PATHINFO_EXTENSION);
                $mimeType = 'image/' . $extension;
                $base64 = base64_encode($imageData);
                $component->state("data:{$mimeType};base64,{$base64}");
            }
        });
    }
}