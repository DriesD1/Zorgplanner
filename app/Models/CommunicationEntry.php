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
}