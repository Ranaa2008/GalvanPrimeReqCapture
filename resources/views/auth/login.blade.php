<x-guest-layout>
    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">

    <div class="max-w-sm mx-auto">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 text-center">Welcome Back</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-2">Please login to your account</p>
        </div>

        <!-- Login Method Tabs -->
        <div class="mb-6">
        <div class="flex border-b border-gray-200 dark:border-gray-600">
            <button type="button" onclick="switchTab('email')" id="tab-email" class="tab-button flex-1 py-3 px-4 text-center font-medium text-sm border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 transition-colors">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email
            </button>
            <button type="button" onclick="switchTab('phone')" id="tab-phone" class="tab-button flex-1 py-3 px-4 text-center font-medium text-sm border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500 transition-colors">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                Phone
            </button>
            <button type="button" onclick="switchTab('username')" id="tab-username" class="tab-button flex-1 py-3 px-4 text-center font-medium text-sm border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500 transition-colors">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Username
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <input type="hidden" name="login_type" id="login_type" value="email">

        <!-- Email Section -->
        <div id="email-section" class="login-section">
            <div class="mb-4">
                <x-input-label for="email-input" :value="__('Email Address')" />
                <x-text-input id="email-input" class="block mt-1 w-full" type="email" name="email_temp" :value="old('login_type') === 'email' ? old('login') : ''" autofocus placeholder="Enter your email" />
            </div>
        </div>

        <!-- Phone Section -->
        <div id="phone-section" class="login-section hidden">
            <div class="mb-4">
                <x-input-label for="phone-input" :value="__('Phone Number')" />
                <input type="tel" id="phone-input" name="phone_temp" value="{{ old('login_type') === 'phone' ? old('login') : '' }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            </div>
        </div>

        <!-- Username Section -->
        <div id="username-section" class="login-section hidden">
            <div class="mb-4">
                <x-input-label for="username-input" :value="__('Username')" />
                <x-text-input id="username-input" class="block mt-1 w-full" type="text" name="username_temp" :value="old('login_type') === 'username' ? old('login') : ''" placeholder="Enter your username" />
            </div>
        </div>

        <!-- Errors -->
        <x-input-error :messages="$errors->get('login')" class="mt-2 mb-4" />

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 rounded-md focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center py-3 bg-blue-600 hover:bg-blue-700">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Don't have an account?</span>
            <a href="{{ route('register') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">Sign up</a>
        </div>
    </form>

    <!-- intl-tel-input JS -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>

    <script>
        let iti = null;

        function initPhoneInput() {
            const phoneInput = document.querySelector("#phone-input");
            if (phoneInput && !iti) {
                iti = window.intlTelInput(phoneInput, {
                    initialCountry: "auto",
                    geoIpLookup: callback => {
                        fetch("https://ipapi.co/json")
                            .then(res => res.json())
                            .then(data => callback(data.country_code))
                            .catch(() => callback("us"));
                    },
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
                    separateDialCode: true,
                    preferredCountries: ["us", "gb", "in"],
                    autoPlaceholder: "aggressive",
                });
            }
        }

        function switchTab(type) {
            // Update hidden input
            document.getElementById('login_type').value = type;

            // Update tabs styling
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            document.getElementById('tab-' + type).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('tab-' + type).classList.add('border-blue-600', 'text-blue-600');

            // Disable validation and clear values on all input fields
            const emailInput = document.getElementById('email-input');
            const phoneInput = document.getElementById('phone-input');
            const usernameInput = document.getElementById('username-input');
            
            emailInput.removeAttribute('required');
            phoneInput.removeAttribute('required');
            usernameInput.removeAttribute('required');

            // Hide all sections
            document.querySelectorAll('.login-section').forEach(section => {
                section.classList.add('hidden');
            });

            // Show selected section and enable validation for active field
            if (type === 'email') {
                // Clear other fields
                phoneInput.value = '';
                usernameInput.value = '';
                
                document.getElementById('email-section').classList.remove('hidden');
                emailInput.setAttribute('required', 'required');
                emailInput.focus();
            } else if (type === 'phone') {
                // Clear other fields
                emailInput.value = '';
                usernameInput.value = '';
                
                document.getElementById('phone-section').classList.remove('hidden');
                phoneInput.setAttribute('required', 'required');
                setTimeout(() => {
                    initPhoneInput();
                    phoneInput.focus();
                }, 100);
            } else {
                // Clear other fields
                emailInput.value = '';
                phoneInput.value = '';
                
                document.getElementById('username-section').classList.remove('hidden');
                usernameInput.setAttribute('required', 'required');
                usernameInput.focus();
            }
        }

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginType = document.getElementById('login_type').value;
            
            // Remove existing login field if any
            const existingLogin = this.querySelector('input[name="login"]');
            if (existingLogin) existingLogin.remove();
            
            // Create new hidden field
            const loginField = document.createElement('input');
            loginField.type = 'hidden';
            loginField.name = 'login';

            if (loginType === 'email') {
                loginField.value = document.getElementById('email-input').value;
            } else if (loginType === 'phone') {
                if (iti && iti.isValidNumber()) {
                    loginField.value = iti.getNumber();
                } else {
                    e.preventDefault();
                    alert('Please enter a valid phone number');
                    return false;
                }
            } else {
                loginField.value = document.getElementById('username-input').value;
            }

            this.appendChild(loginField);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Remove required from all fields initially
            document.getElementById('email-input').removeAttribute('required');
            document.getElementById('phone-input').removeAttribute('required');
            document.getElementById('username-input').removeAttribute('required');
            
            // Restore tab on page load if there were errors
            const oldLoginType = "{{ old('login_type', 'email') }}";
            switchTab(oldLoginType);
        });
    </script>

    <style>
        .tab-button {
            cursor: pointer;
        }
        .iti {
            width: 100%;
        }
        .iti__country-list {
            max-height: 200px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            z-index: 100;
        }
        .login-section {
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    </div>
</x-guest-layout>