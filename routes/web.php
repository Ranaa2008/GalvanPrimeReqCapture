<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RequirementController;
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

    // Projects (role/permission-based access)
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/my', [ProjectController::class, 'myProjects'])->name('projects.my');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Requirements (client + developer workflows)
    Route::get('/requirements/my', [RequirementController::class, 'index'])->name('requirements.my');
    Route::get('/requirements/unread', [RequirementController::class, 'unread'])->name('requirements.unread');
    Route::get('/requirements/create', [RequirementController::class, 'create'])->name('requirements.create');
    Route::post('/requirements', [RequirementController::class, 'store'])->name('requirements.store');
    Route::get('/requirements/{requirement}', [RequirementController::class, 'show'])->name('requirements.show');
    Route::get('/requirements/{requirement}/audio', [RequirementController::class, 'audio'])->name('requirements.audio');
    Route::get('/requirements/{requirement}/voice', [RequirementController::class, 'voice'])->name('requirements.voice');
    Route::get('/requirements/{requirement}/edit', [RequirementController::class, 'edit'])->name('requirements.edit');
    Route::put('/requirements/{requirement}', [RequirementController::class, 'update'])->name('requirements.update');
    Route::delete('/requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');
    Route::patch('/requirements/{requirement}/status', [RequirementController::class, 'updateStatus'])->name('requirements.status');
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
});

require __DIR__.'/auth.php';
