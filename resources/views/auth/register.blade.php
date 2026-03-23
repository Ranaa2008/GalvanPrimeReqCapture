<x-guest-layout>
    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center">Create Account</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-2">Join us today! Fill in your details below</p>
    </div>

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <!-- 2 Column Grid - Responsive -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-6">
            <!-- Left Column -->
            <div class="space-y-5">
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" />
                    <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Phone Number (Primary) -->
                <div>
                    <x-input-label for="phone_number" :value="__('Phone Number')" />
                    <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required
                        class="block mt-1.5 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <input type="hidden" id="phone_number_full" name="phone_number_full">
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" />
                    <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="john@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-5">
                <!-- Username -->
                <div>
                    <x-input-label for="username" :value="__('Username')" />
                    <x-text-input id="username" class="block mt-1.5 w-full" type="text" name="username" :value="old('username')" required placeholder="johndoe123" />
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <!-- Phone Number (Secondary - Optional) -->
                <div>
                    <x-input-label for="phone_number_secondary" :value="__('Secondary Phone (Optional)')" />
                    <input type="tel" id="phone_number_secondary" name="phone_number_secondary" value="{{ old('phone_number_secondary') }}"
                        class="block mt-1.5 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <input type="hidden" id="phone_number_secondary_full" name="phone_number_secondary_full">
                    <x-input-error :messages="$errors->get('phone_number_secondary')" class="mt-2" />
                </div>

                <!-- Address -->
                <div>
                    <x-input-label for="address" :value="__('Address (Optional)')" />
                    <x-text-input id="address" class="block mt-1.5 w-full" type="text" name="address" :value="old('address')" placeholder="Enter your full address" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div>
            <x-primary-button class="w-full justify-center py-3 bg-blue-600 hover:bg-blue-700">
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Already have an account?</span>
            <a href="{{ route('login') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">Login</a>
        </div>
    </form>

    <!-- intl-tel-input JS -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <script>
        // Initialize intl-tel-input for primary phone
        const phoneInput = document.querySelector("#phone_number");
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "lk",
            geoIpLookup: callback => {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("lk"));
            },
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
            separateDialCode: false,
            nationalMode: false,
            formatOnDisplay: true,
            autoFormat: true,
            preferredCountries: ["lk", "in", "us", "gb"],
            autoPlaceholder: "aggressive",
        });

        // Initialize intl-tel-input for secondary phone
        const phoneInputSecondary = document.querySelector("#phone_number_secondary");
        const itiSecondary = window.intlTelInput(phoneInputSecondary, {
            initialCountry: "lk",
            geoIpLookup: callback => {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("lk"));
            },
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
            separateDialCode: false,
            nationalMode: false,
            formatOnDisplay: true,
            autoFormat: true,
            preferredCountries: ["lk", "in", "us", "gb"],
            autoPlaceholder: "aggressive",
        });

        // Auto-format phone number as user types (primary)
        phoneInput.addEventListener('input', function() {
            const formatted = iti.getNumber();
            if (formatted) {
                phoneInput.value = formatted;
            }
        });

        phoneInput.addEventListener('countrychange', function() {
            const formatted = iti.getNumber();
            if (formatted && phoneInput.value) {
                phoneInput.value = formatted;
            }
        });

        // Auto-format phone number as user types (secondary)
        phoneInputSecondary.addEventListener('input', function() {
            const formatted = itiSecondary.getNumber();
            if (formatted) {
                phoneInputSecondary.value = formatted;
            }
        });

        phoneInputSecondary.addEventListener('countrychange', function() {
            const formatted = itiSecondary.getNumber();
            if (formatted && phoneInputSecondary.value) {
                phoneInputSecondary.value = formatted;
            }
        });

        // Form submission - format phone numbers
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Get full international format for primary phone
            const fullNumber = iti.getNumber();
            document.getElementById('phone_number').value = fullNumber;
            
            // Get full international format for secondary phone if entered
            if (phoneInputSecondary.value.trim() !== '') {
                const fullNumberSecondary = itiSecondary.getNumber();
                document.getElementById('phone_number_secondary').value = fullNumberSecondary;
            }

            // Validate phone numbers
            if (!iti.isValidNumber()) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return false;
            }

            if (phoneInputSecondary.value.trim() !== '' && !itiSecondary.isValidNumber()) {
                e.preventDefault();
                alert('Please enter a valid secondary phone number or leave it empty');
                return false;
            }
        });
    </script>

    <style>
        .iti {
            width: 100%;
        }
        .iti__country-list {
            max-height: 200px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
    </style>
</x-guest-layout>
