<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Email OTP Verification
    Route::get('verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'show'])
        ->name('verification.email');
    
    Route::post('verify-email/send', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'sendOtp'])
        ->middleware('throttle:3,1')
        ->name('verification.email.send');
    
    Route::post('verify-email/verify', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->name('verification.email.verify');

    // Phone OTP Verification
    Route::get('verify-phone', [\App\Http\Controllers\Auth\PhoneVerificationController::class, 'show'])
        ->name('verification.phone');
    
    Route::post('verify-phone/send', [\App\Http\Controllers\Auth\PhoneVerificationController::class, 'sendOtp'])
        ->middleware('throttle:3,1')
        ->name('verification.phone.send');
    
    Route::post('verify-phone/verify', [\App\Http\Controllers\Auth\PhoneVerificationController::class, 'verify'])
        ->name('verification.phone.verify');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
