<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookieConsent extends Model
{
    protected $fillable = ['ip_hash', 'user_id', 'consent_type', 'user_agent_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
