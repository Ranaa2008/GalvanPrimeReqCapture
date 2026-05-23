<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmailVerification;
use App\Models\PhoneVerification;
use App\Services\OtpService;
use App\Services\SmsService;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected $otpService;
    protected $smsService;

    public function __construct(OtpService $otpService, SmsService $smsService)
    {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('pages.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldEmail = $user->email;
        $oldPhone = $user->phone_number;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number,' . $user->id],
            'phone_number_secondary' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Format phone numbers
        $validated['phone_number'] = $this->formatPhoneNumber($validated['phone_number']);
        if (!empty($validated['phone_number_secondary'])) {
            $validated['phone_number_secondary'] = $this->formatPhoneNumber($validated['phone_number_secondary']);
        }

        // Check if email changed
        if ($validated['email'] !== $oldEmail) {
            $validated['email_verified'] = false;
        }

        // Check if phone changed
        if ($validated['phone_number'] !== $oldPhone) {
            $validated['phone_verified'] = false;
        }

        // Handle avatar upload (Cloudinary)
        if ($request->hasFile('avatar')) {
            try {
                $uploadApi = new UploadApi();
                
                // If user had a previous avatar, attempt to destroy it first
                if (!empty($user->avatar_public_id)) {
                    try {
                        $uploadApi->destroy($user->avatar_public_id, ['resource_type' => 'image']);
                    } catch (\Exception $e) {
                        // ignore deletion errors
                        \Log::warning('Failed to delete previous Cloudinary image', ['error' => $e->getMessage()]);
                    }
                }

                // Upload new avatar to Cloudinary
                $publicId = 'profiles/user_' . $user->id . '_' . time();
                $uploadResult = $uploadApi->upload($request->file('avatar')->getRealPath(), [
                    'public_id' => $publicId,
                    'overwrite' => true,
                    'resource_type' => 'image',
                ]);

                $validated['avatar_url'] = $uploadResult['secure_url'] ?? $uploadResult['url'];
                $validated['avatar_public_id'] = $uploadResult['public_id'];
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed', ['error' => $e->getMessage()]);
                return Redirect::back()->with('error', 'Failed to upload profile image. Please try again.');
            }
        }

        $user->fill($validated);
        $user->save();

        $message = 'Profile updated successfully.';
        if ($validated['email'] !== $oldEmail || $validated['phone_number'] !== $oldPhone) {
            $message .= ' Please verify your updated contact information.';
        }

        return Redirect::route('profile.edit')->with('success', $message);
    }

    /**
     * Send Email OTP
     */
    public function sendEmailOtp(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is blocked
        if ($user->isBlocked()) {
            $manager = $user->managedBy;
            $managerName = $manager ? $manager->name : 'System Administrator';
            return back()->with('error', "Your account has been blocked by {$managerName}. You cannot verify your email.");
        }

        // Check if user can request new OTP
        $lastVerification = EmailVerification::where('user_id', $user->id)
            ->where('email', $user->email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$this->otpService->canRequestNewOtp($lastVerification)) {
            $secondsRemaining = now()->diffInSeconds($lastVerification->expires_at);
            return back()->with('error', "Please wait {$secondsRemaining} seconds before requesting a new OTP.");
        }

        // Generate OTP
        $otpCode = $this->otpService->generateOtp();
        $expiresAt = $this->otpService->getExpiryTime();

        // Save OTP to database
        EmailVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => $otpCode,
            'failed_attempts' => 0,
            'expires_at' => $expiresAt,
        ]);

        // Send email
        try {
            Mail::raw(
                "Your GalvanPrime ERP email verification code is: {$otpCode}\n\n" .
                "This code will expire in 5 minutes.\n\n" .
                "If you didn't request this code, please ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Email Verification Code - GalvanPrime ERP');
                }
            );

            return back()->with('email_otp_sent', true);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send verification email. Please try again.');
        }
    }

    /**
     * Verify Email OTP
     */
    public function verifyEmailOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Get the latest unverified verification record
        $verification = EmailVerification::where('user_id', $user->id)
            ->where('email', $user->email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['otp_code' => 'No verification request found. Please request a new code.']);
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new code.']);
        }

        // Check if locked due to too many failed attempts
        if ($verification->isLocked()) {
            return back()->withErrors(['otp_code' => 'Too many failed attempts. Please request a new verification code.']);
        }

        // Check if OTP matches
        if ($verification->otp_code !== $request->otp_code) {
            $verification->incrementFailedAttempts();
            $remainingAttempts = 5 - $verification->failed_attempts;
            
            if ($remainingAttempts > 0) {
                return back()->withErrors(['otp_code' => "Invalid verification code. {$remainingAttempts} attempts remaining."]);
            } else {
                return back()->withErrors(['otp_code' => 'Too many failed attempts. Please request a new verification code.']);
            }
        }

        // Mark as verified
        $verification->update(['verified_at' => now()]);
        $user->update(['email_verified' => true]);

        return back()->with('success', 'Email verified successfully!');
    }

    /**
     * Send Phone OTP
     */
    public function sendPhoneOtp(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is blocked
        if ($user->isBlocked()) {
            $manager = $user->managedBy;
            $managerName = $manager ? $manager->name : 'System Administrator';
            return back()->with('error', "Your account has been blocked by {$managerName}. You cannot verify your phone.");
        }

        // Check if user can request new OTP
        $lastVerification = PhoneVerification::where('user_id', $user->id)
            ->where('phone_number', $user->phone_number)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$this->otpService->canRequestNewOtp($lastVerification)) {
            $secondsRemaining = now()->diffInSeconds($lastVerification->expires_at);
            return back()->with('error', "Please wait {$secondsRemaining} seconds before requesting a new OTP.");
        }

        // Generate OTP
        $otpCode = $this->otpService->generateOtp();
        $expiresAt = $this->otpService->getExpiryTime();

        // Save OTP to database
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'otp_code' => $otpCode,
            'failed_attempts' => 0,
            'expires_at' => $expiresAt,
        ]);

        // Send SMS
        if ($this->smsService->sendOtp($user->phone_number, $otpCode)) {
            return back()->with('phone_otp_sent', true);
        }

        // Log OTP only in local/development environment for debugging
        if (app()->environment('local', 'development')) {
            \Log::info('SMS failed - OTP for testing', ['otp' => $otpCode, 'phone' => $user->phone_number]);
        }

        return back()->with('phone_error', 'Failed to send SMS. Please try again later.');
    }

    /**
     * Verify Phone OTP
     */
    public function verifyPhoneOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Get the latest unverified verification record
        $verification = PhoneVerification::where('user_id', $user->id)
            ->where('phone_number', $user->phone_number)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['otp_code' => 'No verification request found. Please request a new code.']);
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['otp_code' => 'Verification code has expired. Please request a new code.']);
        }

        // Check if locked due to too many failed attempts
        if ($verification->isLocked()) {
            return back()->withErrors(['otp_code' => 'Too many failed attempts. Please request a new verification code.']);
        }

        // Check if OTP matches
        if ($verification->otp_code !== $request->otp_code) {
            $verification->incrementFailedAttempts();
            $remainingAttempts = 5 - $verification->failed_attempts;
            
            if ($remainingAttempts > 0) {
                return back()->withErrors(['otp_code' => "Invalid verification code. {$remainingAttempts} attempts remaining."]);
            } else {
                return back()->withErrors(['otp_code' => 'Too many failed attempts. Please request a new verification code.']);
            }
        }

        // Mark as verified
        $verification->update(['verified_at' => now()]);
        $user->update(['phone_verified' => true]);

        return back()->with('success', 'Phone verified successfully!');
    }

    /**
     * Format phone number to international standard (E.164)
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '94' . substr($cleaned, 1);
        }
        
        if (!str_starts_with($cleaned, '94')) {
            $cleaned = '94' . $cleaned;
        }
        
        return '+' . $cleaned;
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
