<x-guest-layout>
    <div class="max-w-sm mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 text-center">Verify Your Phone</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-2">
                We've sent a 6-digit code to <strong>{{ Auth::user()->phone_number }}</strong>
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Verify OTP Form -->
        <form method="POST" action="{{ route('verification.phone.verify') }}">
            @csrf

            <div class="mb-4">
                <x-input-label for="otp_code" :value="__('Enter Verification Code')" />
                <x-text-input 
                    id="otp_code" 
                    class="block mt-1 w-full text-center text-2xl tracking-widest" 
                    type="text" 
                    name="otp_code" 
                    maxlength="6"
                    placeholder="000000"
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('otp_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mb-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                    Verify Phone
                </button>
            </div>
        </form>

        <!-- Send OTP Form -->
        <form method="POST" action="{{ route('verification.phone.send') }}">
            @csrf
            <div class="text-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Didn't receive the code?</span>
                <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold ml-1">
                    Resend Code
                </button>
            </div>
        </form>

        @if ($errors->has('otp'))
            <div class="mt-4 p-4 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-lg text-sm">
                {{ $errors->first('otp') }}
            </div>
        @endif

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-xs text-blue-600 dark:text-blue-400">
                <strong>Note:</strong> The verification code is valid for 5 minutes. You can request a new code after it expires.
            </p>
        </div>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
