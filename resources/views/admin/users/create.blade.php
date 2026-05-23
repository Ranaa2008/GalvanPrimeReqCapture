@extends('layouts.app')

@push('styles')
    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New User</h1>
</div>

<div class="max-w-4xl">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
        
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username *</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror" required>
                    @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" required>
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('phone_number') border-red-500 @enderror" required>
                    @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="phone_number_secondary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Secondary Phone (Optional)</label>
                <input type="tel" name="phone_number_secondary" id="phone_number_secondary" value="{{ old('phone_number_secondary') }}" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('phone_number_secondary') border-red-500 @enderror">
                @error('phone_number_secondary')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                <textarea name="address" id="address" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                    <input type="password" name="password" id="password" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" required>
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Assign Roles</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($roles as $role)
                    <div class="flex items-center p-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            {{ $role->name }}
                            @if($role->superiority)
                                <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">(Level {{ $role->superiority }})</span>
                            @endif
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-6" 
                x-data="{
                    availableManagers: [],
                    rolesData: {{ Js::from($roles) }},
                    
                    init() {
                        console.log('Manager selector initialized');
                        console.log('Available roles:', this.rolesData);
                        
                        // Watch for role checkbox changes
                        document.querySelectorAll('input[name=\'roles[]\']').forEach(checkbox => {
                            checkbox.addEventListener('change', () => {
                                console.log('Role checkbox changed:', checkbox.value, checkbox.checked);
                                this.updateManagers();
                            });
                        });
                    },
                    
                    async updateManagers() {
                        // Get all checked roles
                        const checkedRoles = Array.from(document.querySelectorAll('input[name=\'roles[]\']:checked'))
                            .map(cb => cb.value);
                        
                        console.log('Checked roles:', checkedRoles);
                        
                        if (checkedRoles.length === 0) {
                            this.availableManagers = [];
                            return;
                        }
                        
                        // Find the highest superiority (lowest number) role selected
                        const selectedRoleObjects = this.rolesData.filter(r => checkedRoles.includes(r.name));
                        
                        console.log('Selected role objects:', selectedRoleObjects);
                        
                        if (selectedRoleObjects.length === 0) {
                            console.log('No matching role objects found');
                            this.availableManagers = [];
                            return;
                        }
                        
                        if (!selectedRoleObjects[0].superiority) {
                            console.log('First role has no superiority:', selectedRoleObjects[0]);
                            this.availableManagers = [];
                            return;
                        }
                        
                        // Get the role with lowest superiority number (highest rank)
                        const highestRole = selectedRoleObjects.reduce((prev, curr) => 
                            (curr.superiority < prev.superiority) ? curr : prev
                        );
                        
                        console.log('Highest role selected:', highestRole);
                        
                        // Fetch managers for this role level
                        try {
                            const url = `{{ route('admin.users.managers-by-role') }}?role_id=${highestRole.id}`;
                            console.log('Fetching managers from:', url);
                            
                            const response = await fetch(url);
                            const data = await response.json();
                            
                            console.log('Received managers:', data);
                            this.availableManagers = data;
                        } catch (error) {
                            console.error('Error fetching managers:', error);
                            this.availableManagers = [];
                        }
                    }
                }"
                x-init="init()">
                <label for="managed_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Managed By (Superior/Manager)
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">- Select a role first to see available managers</span>
                </label>
                <select name="managed_by" id="managed_by" x-ref="managerSelect"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500"
                    :disabled="!availableManagers.length">
                    <option value="">-- Assign to myself ({{ auth()->user()->name }}) --</option>
                    <template x-for="manager in availableManagers" :key="manager.id">
                        <option :value="manager.id" x-text="`${manager.name} (${manager.email}) - ${manager.roles}`"></option>
                    </template>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="!availableManagers.length">
                    Select a role above to see available managers for that user level.
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="availableManagers.length">
                    The assigned manager will have authority over this user's account.
                </p>
                @error('managed_by')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition duration-200">
                    Cancel
                </a>
                <button type="submit" id="createUserBtn" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
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

        // Form submission - format phone numbers and validate
        document.querySelector('form').addEventListener('submit', function(e) {
            // Get full international format for primary phone
            const fullNumber = iti.getNumber();
            phoneInput.value = fullNumber;
            
            // Get full international format for secondary phone if entered
            if (phoneInputSecondary.value.trim() !== '') {
                const fullNumberSecondary = itiSecondary.getNumber();
                phoneInputSecondary.value = fullNumberSecondary;
            }

            // Validate primary phone number
            if (!iti.isValidNumber()) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                phoneInput.focus();
                return false;
            }

            // Validate secondary phone if provided
            if (phoneInputSecondary.value.trim() !== '' && !itiSecondary.isValidNumber()) {
                e.preventDefault();
                alert('Please enter a valid secondary phone number or leave it empty');
                phoneInputSecondary.focus();
                return false;
            }
        });
    </script>
@endpush
