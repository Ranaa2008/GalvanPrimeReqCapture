<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Client\RequirementController as ClientRequirementController;
use App\Http\Controllers\Developer\RequirementController as DeveloperRequirementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Profile verification routes
    Route::post('/profile/send-email-otp', [ProfileController::class, 'sendEmailOtp'])->name('profile.send-email-otp');
    Route::post('/profile/verify-email-otp', [ProfileController::class, 'verifyEmailOtp'])->name('profile.verify-email-otp');
    Route::post('/profile/send-phone-otp', [ProfileController::class, 'sendPhoneOtp'])->name('profile.send-phone-otp');
    Route::post('/profile/verify-phone-otp', [ProfileController::class, 'verifyPhoneOtp'])->name('profile.verify-phone-otp');
});

// Admin routes (for super-admin and admin roles only)
Route::prefix('admin')->name('admin.')->middleware(['auth', App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Management routes (permission-based access for all management users)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // User Management
    Route::get('/users/managers-by-role', [UserController::class, 'getManagersByRole'])->name('users.managers-by-role');

    // Impersonation (view as subordinate)
    Route::get('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

    Route::resource('users', UserController::class);
    Route::post('/users/{user}/verify-email', [UserController::class, 'verifyEmail'])->name('users.verify-email');
    Route::post('/users/{user}/verify-phone', [UserController::class, 'verifyPhone'])->name('users.verify-phone');
    Route::post('/users/{user}/deverify-email', [UserController::class, 'deverifyEmail'])->name('users.deverify-email');
    Route::post('/users/{user}/deverify-phone', [UserController::class, 'deverifyPhone'])->name('users.deverify-phone');
    Route::post('/users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block');
    
    // Role Management
    Route::resource('roles', RoleController::class);
    
    // Permission Management
    Route::resource('permissions', PermissionController::class);

    // Project management and assignment
    Route::resource('projects', ProjectController::class)->except(['show']);
});

Route::prefix('client')->name('client.')->middleware(['auth'])->group(function () {
    Route::get('/requirements', [ClientRequirementController::class, 'index'])
        ->middleware('can:submit-requirements')
        ->name('requirements.index');
    Route::get('/requirements/create', [ClientRequirementController::class, 'create'])
        ->middleware('can:submit-requirements')
        ->name('requirements.create');
    Route::post('/requirements', [ClientRequirementController::class, 'store'])
        ->middleware('can:submit-requirements')
        ->name('requirements.store');
    Route::get('/requirements/{requirement}/edit', [ClientRequirementController::class, 'edit'])
        ->middleware('can:edit-own-requirements')
        ->name('requirements.edit');
    Route::put('/requirements/{requirement}', [ClientRequirementController::class, 'update'])
        ->middleware('can:edit-own-requirements')
        ->name('requirements.update');
    Route::delete('/requirements/{requirement}', [ClientRequirementController::class, 'destroy'])
        ->middleware('can:delete-own-requirements')
        ->name('requirements.destroy');
});

Route::prefix('developer')->name('developer.')->middleware(['auth', 'can:view-assigned-requirements'])->group(function () {
    Route::get('/requirements', [DeveloperRequirementController::class, 'index'])->name('requirements.index');
    Route::get('/requirements/{requirement}', [DeveloperRequirementController::class, 'show'])->name('requirements.show');
});

require __DIR__.'/auth.php';
