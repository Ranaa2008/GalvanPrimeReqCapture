<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Additional Styles -->
        @stack('styles')
        
        <style>
            /* Scale everything to 90% to fit more content */
            html {
                font-size: 14px; /* Default is usually 16px, this gives ~87.5% */
            }
            body {
                font-size: 0.875rem; /* 14px base font size */
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-white dark:bg-gray-950" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen">
            <!-- Top Bar -->
            <x-layout.topbar />

            <!-- Sidebar -->
            <x-layout.sidebar />

            <!-- Main Content -->
            <main class="pt-14 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-56' : 'lg:ml-0'">
                <div class="mx-auto px-3 sm:px-4 lg:px-6 py-4">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="mb-6">
                            <div class="max-w-7xl mx-auto">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                <!-- Verification Alert for Unverified Users (except super-admins) -->
                @auth
                    @if(!auth()->user()->hasRole('super-admin') && (!auth()->user()->email_verified || !auth()->user()->phone_verified))
                        <div class="max-w-7xl mx-auto mb-6">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Account Verification Required</h3>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                            Please verify your 
                                            @if(!auth()->user()->email_verified) email @endif
                                            @if(!auth()->user()->email_verified && !auth()->user()->phone_verified) and @endif
                                            @if(!auth()->user()->phone_verified) phone number @endif
                                            to access all features.
                                        </p>
                                        <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:text-yellow-900 dark:hover:text-yellow-100 mt-2 inline-block">
                                            Verify Now →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth

                <!-- Page Content -->
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    
    <!-- Toast Notifications -->
    <x-toast />
    
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
