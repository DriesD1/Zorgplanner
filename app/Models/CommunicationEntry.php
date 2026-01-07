<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationEntry extends Model
{
    protected $guarded = []; // Alles mag ingevuld worden

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function booted(): void
    {
        static::creating(function (CommunicationEntry $entry) {
            if (! $entry->organization_id && $entry->house) {
                $entry->organization_id = $entry->house->organization_id;
            }
        });
    }
}