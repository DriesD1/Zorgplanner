<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $guarded = [];

    // Dit zorgt dat datums nooit als tekst worden gezien, 
    // maar altijd als stabiele datum-objecten.
    protected $casts = [
        'date' => 'date', 
        'is_planned' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}