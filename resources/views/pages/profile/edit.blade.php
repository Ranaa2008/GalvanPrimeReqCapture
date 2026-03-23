@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile</h1>
</div>

<div class="space-y-6">
        <!-- Split Layout: Verification (Left) + Profile Edit (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Verification Sections (1/3 width on large screens) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Email Verification -->
                <x-profile.email-verification :user="auth()->user()" />

                <!-- Phone Verification -->
                <x-profile.phone-verification :user="auth()->user()" />
            </div>

            <!-- Right Side: Profile Edit Form (2/3 width on large screens) -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Profile Information</h3>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" 
                        x-data="{
                            previewUrl: '{{ auth()->user()->avatar_url ?? '' }}',
                            defaultAvatar: '{{ 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=ffffff&color=000' }}',
                            previewImage(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        this.previewUrl = e.target.result;
                                    };
                                    reader.readAsDataURL(file);
                                }
                            }
                        }">
                        @csrf
                        @method('PATCH')

                        <!-- Avatar Upload with Client-Side Preview -->
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700">
                                <img :src="previewUrl || defaultAvatar" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <label for="avatar" class="block text-sm font-medium text-gray-900 dark:text-white">Profile Photo</label>
                                <input type="file" name="avatar" id="avatar" accept="image/*" @change="previewImage"
                                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-gray-200 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300 hover:file:bg-gray-300 dark:hover:file:bg-gray-600" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. Upload a JPG/PNG image (max 2MB). This will be stored on Cloudinary.</p>
                                @error('avatar')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- 2-Column Grid for Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Username</label>
                                <input type="text" name="username" id="username" value="{{ old('username', auth()->user()->username) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @error('username')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @if(auth()->user()->email !== old('email') && !auth()->user()->hasRole('super-admin'))
                                    <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">⚠️ Changing your email will require re-verification</p>
                                @endif
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Phone Number</label>
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', auth()->user()->phone_number) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @if(auth()->user()->phone_number !== old('phone_number') && !auth()->user()->hasRole('super-admin'))
                                    <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">⚠️ Changing your phone will require re-verification</p>
                                @endif
                                @error('phone_number')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Secondary Phone (Optional) -->
                            <div>
                                <label for="phone_number_secondary" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Secondary Phone (Optional)</label>
                                <input type="tel" name="phone_number_secondary" id="phone_number_secondary" value="{{ old('phone_number_secondary', auth()->user()->phone_number_secondary) }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @error('phone_number_secondary')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address - Full Width -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Address (Optional)</label>
                                <textarea name="address" id="address" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">{{ old('address', auth()->user()->address) }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-6 py-2 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Change Password</h3>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- 2-Column Grid for Password Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Current Password - Full Width -->
                            <div class="md:col-span-2">
                                <label for="current_password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Current Password</label>
                                <input type="password" name="current_password" id="current_password" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">New Password</label>
                                <input type="password" name="password" id="password" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-6 py-2 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
