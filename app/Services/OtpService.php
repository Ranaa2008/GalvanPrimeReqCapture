<?php

namespace App\Services;

use Carbon\Carbon;

class OtpService
{
    /**
     * Generate a 6-digit OTP code
     */
    public function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get OTP expiry time (5 minutes from now)
     */
    public function getExpiryTime(): Carbon
    {
        return Carbon::now()->addMinutes(5);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(Carbon $expiresAt): bool
    {
        return Carbon::now()->isAfter($expiresAt);
    }

    /**
     * Check if user can request new OTP (5 minutes must pass)
     */
    public function canRequestNewOtp($lastRequest): bool
    {
        if (!$lastRequest) {
            return true;
        }

        $expiresAt = $lastRequest->expires_at;
        
        // User can request new OTP only after the previous one expires
        return $this->isExpired($expiresAt);
    }
}
