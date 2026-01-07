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

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function booted()
    {
        static::creating(function (Client $client) {
            if (! $client->organization_id && $client->house) {
                $client->organization_id = $client->house->organization_id;
            }
        });

        static::deleting(function ($client) {
            if ($client->feet_drawing_path && Storage::disk('public')->exists($client->feet_drawing_path)) {
                Storage::disk('public')->delete($client->feet_drawing_path);
            }
        });
    }
}