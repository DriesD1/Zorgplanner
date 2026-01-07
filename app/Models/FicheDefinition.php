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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function booted(): void
    {
        static::creating(function (FicheDefinition $definition) {
            if (! $definition->organization_id && $definition->user) {
                $definition->organization_id = $definition->user->organization_id;
            }
        });
    }
}
