<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    protected $fillable = [
        'subscription_id',
        'token',
        'is_successful',
        'redirected_at',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'redirected_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
