<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public const ROLE_ADVERTISER = 'advertiser';
    public const ROLE_WEBMASTER = 'webmaster';
    public const ROLE_ADMIN = 'admin';

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'advertiser_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'webmaster_id');
    }

    public function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? Hash::make($value) : null,
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
