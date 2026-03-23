<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('services.textlk.url');
        $this->apiToken = config('services.textlk.token');
    }

    /**
     * Send SMS via Text.lk API
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        try {
            $message = "Your GalvanPrime ERP verification code is: {$otpCode}. Valid for 5 minutes. Do not share this code with anyone.";

            $payload = [
                'recipient' => $this->formatPhoneNumber($phoneNumber),
                'message' => $message,
            ];

            // Only add sender_id if configured
            $senderId = config('services.textlk.sender_id');
            if ($senderId) {
                $payload['sender_id'] = $senderId;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/send', $payload);

            $responseData = $response->json();

            // Check if response has error status in the body (even if HTTP status is 200)
            if (isset($responseData['status']) && $responseData['status'] === 'error') {
                Log::error('SMS sending failed - API returned error', [
                    'phone' => $phoneNumber,
                    'error_message' => $responseData['message'] ?? 'Unknown error',
                    'response' => $responseData
                ]);
                return false;
            }

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => $phoneNumber,
                    'response' => $responseData
                ]);
                return true;
            }

            Log::error('SMS sending failed - HTTP error', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format phone number for Text.lk API
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Ensure it starts with country code
        if (!str_starts_with($phoneNumber, '94')) {
            // If starts with 0, replace with 94
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = '94' . substr($phoneNumber, 1);
            }
        }
        
        return $phoneNumber;
    }
}
