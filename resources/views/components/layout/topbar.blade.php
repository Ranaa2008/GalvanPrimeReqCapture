<!-- Top Bar - Fixed -->
<nav class="fixed top-0 left-0 right-0 h-14 bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800 z-50">
    <div class="h-full px-3 flex items-center justify-between">
        <!-- Left Side: Logo + Sidebar Toggle -->
        <div class="flex items-center space-x-3">
            <!-- Sidebar Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <div class="w-7 h-7 bg-gray-900 dark:bg-white rounded-lg flex items-center justify-center">
                    <span class="text-white dark:text-gray-900 font-bold text-xs">GP</span>
                </div>
                <span class="text-lg font-bold text-gray-900 dark:text-white hidden sm:block">GalvanPrime</span>
            </a>
        </div>

        <!-- Right Side: Dark Mode + Notifications + Profile + Logout -->
        <div class="flex items-center space-x-1.5">
            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                <svg x-show="!darkMode" class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="darkMode" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </button>

            <!-- Notifications -->
            <button class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition relative">
                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <!-- Notification Badge -->
                <span class="absolute top-0.5 right-0.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Profile Dropdown -->
            <div x-data="{ profileOpen: false }" class="relative">
                <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                    @if(auth()->user()->avatar_url)
                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-7 h-7 bg-gray-900 dark:bg-white rounded-full flex items-center justify-center">
                            <span class="text-white dark:text-gray-900 font-semibold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="text-sm font-medium text-gray-900 dark:text-white hidden md:block">{{ auth()->user()->name }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="profileOpen" @click.away="profileOpen = false" x-transition class="absolute right-0 mt-1 w-44 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg py-1">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-1.5 text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-800">
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-800">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition" title="Logout">
                    <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>
