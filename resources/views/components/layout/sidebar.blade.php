@php
    $user = auth()->user();
    $userRoles = $user->roles->pluck('name')->toArray();
    $isSuperAdmin = in_array('super-admin', $userRoles);
    $isAdmin = in_array('admin', $userRoles);
    $isUser = in_array('user', $userRoles);
    $isVerified = $user->email_verified && $user->phone_verified;
    // Super-admins don't need verification
    if ($isSuperAdmin) {
        $isVerified = true;
    }
@endphp

<!-- Sidebar - Collapsible -->
<aside 
    x-show="sidebarOpen" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed left-0 top-14 bottom-0 w-56 bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 z-40 overflow-y-auto"
>
    <nav class="p-3 space-y-0.5">
        @if(!$isVerified)
            <!-- Unverified User - Profile Only -->
            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-medium">Profile</span>
            </a>

            <div class="pt-3 px-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">Complete verification to access more features</p>
            </div>
        @else
            <!-- Verified Users - Role-based Navigation -->
            
            <!-- Dashboard (All verified users) -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-medium">Profile</span>
            </a>

            @php
                $currentUser = auth()->user();
                $hasManagementAccess = $isSuperAdmin || $currentUser->hasAnyPermission(['view-users', 'view-roles', 'view-permissions', 'view-projects']);
            @endphp

            @if($hasManagementAccess)
                <!-- Admin Separator -->
                <div class="pt-3 pb-1 px-3">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Management</p>
                </div>
            @endif

            @if($currentUser->hasPermissionTo('view-users'))
                <!-- Users Management -->
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Users</span>
                </a>
            @endif

            @if($currentUser->hasPermissionTo('view-roles'))
                <!-- Roles Management -->
                <a href="{{ route('admin.roles.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="font-medium">Roles</span>
                </a>
            @endif

            @if($currentUser->hasPermissionTo('view-permissions'))
                <!-- Permissions Management -->
                <a href="{{ route('admin.permissions.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.permissions.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="font-medium">Permissions</span>
                </a>
            @endif

            @if($isSuperAdmin || $currentUser->can('view-projects'))
                <!-- Project Assignments -->
                <a href="{{ route('admin.projects.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('admin.projects.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 6V9m0 8l-3 3m3-3l3 3M5 7h10M5 11h6M5 15h4"></path>
                    </svg>
                    <span class="font-medium">Projects</span>
                </a>
            @endif

            @if($currentUser->can('submit-requirements') || $currentUser->can('view-assigned-requirements'))
                <div class="pt-3 pb-1 px-3">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Requirements</p>
                </div>
            @endif

            @if($currentUser->can('submit-requirements'))
                <a href="{{ route('client.requirements.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('client.requirements.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="font-medium">My Requirements</span>
                </a>
            @endif

            @if($currentUser->can('view-assigned-requirements'))
                <a href="{{ route('developer.requirements.index') }}" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg {{ request()->routeIs('developer.requirements.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }} transition" @click="sidebarOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m12-10a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Client Requirements</span>
                </a>
            @endif

            @if($isSuperAdmin)

                <!-- Settings -->
                <a href="#" class="flex items-center space-x-2 px-3 py-2 text-sm rounded-lg text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900 transition" @click="sidebarOpen = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">Settings</span>
                </a>
            @endif
        @endif
    </nav>
</aside>

<!-- Overlay for mobile -->
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
></div>
