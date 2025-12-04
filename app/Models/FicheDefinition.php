<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FicheDefinition extends Model
{
    // Deze regel zegt: "Je mag alle kolommen invullen" (dus ook user_id)
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
