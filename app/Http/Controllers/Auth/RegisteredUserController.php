<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'phone_number_secondary' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Format phone numbers to international standard (E.164)
        $phoneNumber = $this->formatPhoneNumber($request->phone_number);
        $phoneNumberSecondary = $request->phone_number_secondary 
            ? $this->formatPhoneNumber($request->phone_number_secondary) 
            : null;

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $phoneNumber,
            'phone_number_secondary' => $phoneNumberSecondary,
            'address' => $request->address,
            'password' => Hash::make($request->password),
        ]);

        // Do NOT assign role yet - user must be verified first
        // $user->assignRole('user');

        event(new Registered($user));

        Auth::login($user);

        // Redirect to profile page for verification
        return redirect()->route('profile.edit')->with('success', 'Registration successful! Please verify your email and phone number to access all features.');
    }

    /**
     * Format phone number to international standard (E.164)
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0, replace with country code (94 for Sri Lanka)
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '94' . substr($cleaned, 1);
        }
        
        // If it doesn't start with country code, add it
        if (!str_starts_with($cleaned, '94')) {
            $cleaned = '94' . $cleaned;
        }
        
        // Add + prefix for E.164 format
        return '+' . $cleaned;
    }
}
