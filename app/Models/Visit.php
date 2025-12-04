<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'is_planned' => 'boolean',
        'is_done' => 'boolean',
        'is_reported' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}