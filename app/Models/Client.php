<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    protected $guarded = [];

    protected $casts = [
        'fiche_data' => 'array', // Zorgt dat de database JSON omzet naar bruikbare data
        'next_planned_date' => 'date',
    ];

    protected static function booted()
    {
        // Verwijder de feet drawing image wanneer een client wordt verwijderd
        static::deleting(function ($client) {
            if ($client->feet_drawing_path && Storage::disk('public')->exists($client->feet_drawing_path)) {
                Storage::disk('public')->delete($client->feet_drawing_path);
            }
        });
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}