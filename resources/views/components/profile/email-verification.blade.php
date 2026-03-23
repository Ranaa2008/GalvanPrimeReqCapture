@props(['user'])

<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email Verification</h3>
        @if($user->email_verified)
            <span class="flex items-center text-sm text-green-600 dark:text-green-400">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Verified
            </span>
        @else
            <span class="flex items-center text-sm text-yellow-600 dark:text-yellow-400">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Not Verified
            </span>
        @endif
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Email: <strong class="text-gray-900 dark:text-white">{{ $user->email }}</strong>
    </p>

    @if(!$user->email_verified)
        <div x-data="{ 
            countdown: {{ session('email_otp_sent') || $errors->has('otp_code') ? 300 : 0 }},
            canSend: {{ session('email_otp_sent') || $errors->has('otp_code') ? 'false' : 'true' }},
            init() {
                if (!this.canSend) {
                    this.startCountdown();
                }
            },
            startCountdown() {
                if (this.countdown === 0) {
                    this.countdown = 300;
                }
                this.canSend = false;
                const interval = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) {
                        clearInterval(interval);
                        this.canSend = true;
                    }
                }, 1000);
            }
        }">
            @if(session('email_otp_sent'))
                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
                    OTP sent successfully! Check your email.
                </div>
            @endif

            <!-- Send OTP Form -->
            <form method="POST" action="{{ route('profile.send-email-otp') }}" @submit="startCountdown()" x-show="canSend">
                @csrf
                <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                    Send Verification Code
                </button>
            </form>

            <!-- Countdown Timer -->
            <div x-show="!canSend" class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Code sent! You can request a new code in 
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="Math.floor(countdown / 60) + ':' + (countdown % 60).toString().padStart(2, '0')"></span>
                </p>

                <!-- Verify OTP Form -->
                <form method="POST" action="{{ route('profile.verify-email-otp') }}" class="space-y-3">
                    @csrf
                    <input 
                        type="text" 
                        name="otp_code" 
                        maxlength="6" 
                        placeholder="Enter 6-digit code" 
                        class="w-full px-4 py-2 text-center text-2xl tracking-widest border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                        required
                    >
                    <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                        Verify Email
                    </button>
                </form>
            </div>

            @error('otp_code')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @else
        <p class="text-sm text-green-600 dark:text-green-400">Your email has been verified successfully.</p>
    @endif
</div>
