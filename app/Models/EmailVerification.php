<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerification extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'otp_code',
        'failed_attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    public function isLocked(): bool
    {
        return $this->failed_attempts >= 5;
    }

    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_attempts');
    }
}
