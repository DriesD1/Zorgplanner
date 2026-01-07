<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function houses()
    {
        return $this->hasMany(House::class);
    }

    public function ficheDefinitions()
    {
        return $this->hasMany(FicheDefinition::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->organization_id) {
                return;
            }

            $name = $user->name ?? $user->email ?? 'Organisatie';
            $orgId = DB::table('organizations')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->organization_id = $orgId;
        });
    }
}