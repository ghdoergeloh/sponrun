<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'ext_personnel_no',
        'firstname',
        'lastname',
        'email',
        'phone',
        'birthday',
        'street',
        'housenumber',
        'postcode',
        'city',
        'gender',
        'password',
        'confirmed',
        'confirmation_code',
        'wants_newsletter',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'confirmation_code',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'email_verified_at' => 'datetime',
            'confirmed' => 'boolean',
            'wants_newsletter' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function runParticipations(): HasMany
    {
        return $this->hasMany(RunParticipation::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }
}
