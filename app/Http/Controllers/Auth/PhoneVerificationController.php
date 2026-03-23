<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneVerificationController extends Controller
{
    protected $otpService;
    protected $smsService;

    public function __construct(OtpService $otpService, SmsService $smsService)
    {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
    }

    /**
     * Show the phone verification form
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user->email_verified) {
            return redirect()->route('verification.email')
                ->withErrors(['error' => 'Please verify your email first.']);
        }

        if ($user->phone_verified) {
            return redirect()->route('dashboard')
                ->with('success', 'Your account is fully verified!');
        }

        // Check if OTP was already sent recently
        $phoneNumber = $user->phone_number;
        $lastVerification = PhoneVerification::where('user_id', $user->id)
            ->where('phone_number', $phoneNumber)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        // If no OTP sent yet, or last OTP expired, send automatically
        if (!$lastVerification || $lastVerification->expires_at < now()) {
            // Generate OTP
            $otpCode = $this->otpService->generateOtp();
            $expiresAt = $this->otpService->getExpiryTime();

            // Save OTP to database
            PhoneVerification::create([
                'user_id' => $user->id,
                'phone_number' => $phoneNumber,
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt,
            ]);

            // Send SMS
            if ($this->smsService->sendOtp($phoneNumber, $otpCode)) {
                \Log::info('Auto-sent phone OTP successfully', [
                    'phone' => $phoneNumber,
                    'otp_code' => $otpCode,
                ]);
                session()->flash('success', 'Verification code sent to your phone!');
            } else {
                \Log::error('Failed to auto-send phone OTP', [
                    'phone' => $phoneNumber,
                    'otp_code' => $otpCode,
                ]);
                session()->flash('error', 'Failed to send SMS. Please check your phone number or contact support. (OTP saved for manual verification: ' . $otpCode . ')');
            }
        }

        return view('auth.verify-phone');
    }

    /**
     * Send OTP to phone
     */
    public function sendOtp(Request $request)
    {
        $user = Auth::user();
        $phoneNumber = $user->phone_number_full ?? $user->phone_number;

        // Check if user can request new OTP
        $lastVerification = PhoneVerification::where('user_id', $user->id)
            ->where('phone_number', $phoneNumber)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$this->otpService->canRequestNewOtp($lastVerification)) {
            $secondsRemaining = now()->diffInSeconds($lastVerification->expires_at);
            return back()->withErrors([
                'otp' => "Please wait {$secondsRemaining} seconds before requesting a new OTP."
            ]);
        }

        // Generate OTP
        $otpCode = $this->otpService->generateOtp();
        $expiresAt = $this->otpService->getExpiryTime();

        // Save OTP to database
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone_number' => $phoneNumber,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send SMS
        if ($this->smsService->sendOtp($phoneNumber, $otpCode)) {
            return back()->with('success', 'Verification code sent to your phone!');
        }

        return back()->withErrors(['otp' => 'Failed to send SMS. Please try again.']);
    }

    /**
     * Verify the OTP code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $phoneNumber = $user->phone_number_full ?? $user->phone_number;

        $verification = PhoneVerification::where('user_id', $user->id)
            ->where('phone_number', $phoneNumber)
            ->where('otp_code', $request->otp_code)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['otp_code' => 'Invalid verification code.']);
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new one.']);
        }

        // Mark as verified
        $verification->update(['verified_at' => now()]);
        $user->update(['phone_verified' => true]);

        return redirect()->route('dashboard')
            ->with('success', 'Phone verified successfully! Your account is now fully verified.');
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        return $this->sendOtp($request);
    }
}
