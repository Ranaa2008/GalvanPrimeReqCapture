<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the email verification form
     */
    public function show()
    {
        $user = Auth::user();
        
        if ($user->email_verified) {
            return redirect()->route('verification.phone');
        }

        // Check if OTP was already sent recently
        $lastVerification = EmailVerification::where('user_id', $user->id)
            ->where('email', $user->email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        // If no OTP sent yet, or last OTP expired, send automatically
        if (!$lastVerification || $lastVerification->expires_at < now()) {
            // Generate OTP
            $otpCode = $this->otpService->generateOtp();
            $expiresAt = $this->otpService->getExpiryTime();

            // Save OTP to database
            EmailVerification::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt,
            ]);

            // Send email
            try {
                \Log::info('Auto-sending OTP email on verification page load', [
                    'to' => $user->email,
                    'otp_code' => $otpCode,
                ]);

                Mail::raw(
                    "Your GalvanPrime ERP email verification code is: {$otpCode}\n\n" .
                    "This code will expire in 5 minutes.\n\n" .
                    "If you didn't request this code, please ignore this email.",
                    function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Email Verification Code - GalvanPrime ERP');
                    }
                );

                \Log::info('Auto-sent OTP email successfully', [
                    'to' => $user->email,
                ]);

                session()->flash('success', 'Verification code sent to your email!');
            } catch (\Exception $e) {
                \Log::error('Failed to auto-send OTP email', [
                    'error' => $e->getMessage(),
                    'to' => $user->email,
                ]);
            }
        }

        return view('auth.verify-email');
    }

    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request)
    {
        $user = Auth::user();

        // Check if user can request new OTP
        $lastVerification = EmailVerification::where('user_id', $user->id)
            ->where('email', $user->email)
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
        EmailVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send email
        try {
            \Log::info('Attempting to send OTP email', [
                'to' => $user->email,
                'otp_code' => $otpCode,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'from' => config('mail.from.address'),
                ]
            ]);

            Mail::raw(
                "Your GalvanPrime ERP email verification code is: {$otpCode}\n\n" .
                "This code will expire in 5 minutes.\n\n" .
                "If you didn't request this code, please ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Email Verification Code - GalvanPrime ERP');
                }
            );

            \Log::info('OTP email sent successfully', [
                'to' => $user->email,
            ]);

            return back()->with('success', 'Verification code sent to your email!');
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $user->email,
            ]);
            
            return back()->withErrors(['otp' => 'Failed to send email: ' . $e->getMessage()]);
        }
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

        $verification = EmailVerification::where('user_id', $user->id)
            ->where('email', $user->email)
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
        $user->update(['email_verified' => true]);

        return redirect()->route('verification.phone')
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        return $this->sendOtp($request);
    }
}
