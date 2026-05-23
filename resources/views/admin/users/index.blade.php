@extends('layouts.app')

@section('content')
<div x-data="{ open: false, iframeSrc: '', openAs(url) { this.iframeSrc = url; this.open = true; }, close() { this.open = false; this.iframeSrc = ''; } }">
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users Management</h1>
    
    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <!-- Search Form -->
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex-1 sm:flex-initial">
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search users..." 
                    class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent"
                >
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
        
        @can('create-users')
        <a href="{{ route('admin.users.create') }}" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 font-medium py-2 px-4 rounded-lg transition whitespace-nowrap">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create New User
        </a>
        @endcan
    </div>
</div>

@if(request('search'))
    <div class="mb-4 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <span class="text-sm text-blue-800 dark:text-blue-200">
            Showing results for: <strong>{{ request('search') }}</strong>
            <a href="{{ route('admin.users.index') }}" class="ml-2 text-blue-600 dark:text-blue-400 hover:underline">Clear search</a>
        </span>
    </div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Manager</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $user->isBlocked() ? 'opacity-60' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        <div class="flex items-center gap-3">
                            @if($user->avatar_url)
                                <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 flex-shrink-0">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-8 h-8 bg-gray-900 dark:bg-white rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white dark:text-gray-900 font-semibold text-xs">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <div>
                                @php
                                    $canImpersonate = auth()->check()
                                        && $user->id !== auth()->id()
                                        && (auth()->user()->hasRole('super-admin') || auth()->user()->hasPermissionTo('edit-users'))
                                        && auth()->user()->canManage($user);
                                @endphp

                                @if($canImpersonate)
                                    <button
                                        type="button"
                                        class="text-left hover:underline hover:text-gray-900 dark:hover:text-white"
                                        @click="openAs('{{ route('admin.users.impersonate', $user) }}?to={{ urlencode('/dashboard') }}')"
                                    >
                                        {{ $user->name }}
                                    </button>
                                @else
                                    {{ $user->name }}
                                @endif
                                @if($user->isBlocked())
                                    <span class="ml-2 text-xs text-red-600 dark:text-red-400">🔒 Blocked</span>
                                @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">({{ $user->username }})</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            <span>{{ $user->email }}</span>
                            @can('verify-users')
                                @if(!$user->email_verified)
                                <x-tooltip text="Click to verify email" position="top">
                                    <form action="{{ route('admin.users.verify-email', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-5 h-5 rounded transition flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                </x-tooltip>
                                @endif
                            @endcan
                            
                            @can('deverify-users')
                                @if($user->email_verified)
                                <x-tooltip text="Click to remove verification" position="top">
                                    <form action="{{ route('admin.users.deverify-email', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-5 h-5 rounded transition flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </x-tooltip>
                                @endif
                            @endcan
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-2">
                            <span>{{ $user->phone_number }}</span>
                            @can('verify-users')
                                @if(!$user->phone_verified)
                                <x-tooltip text="Click to verify phone" position="top">
                                    <form action="{{ route('admin.users.verify-phone', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-5 h-5 rounded transition flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                </x-tooltip>
                                @endif
                            @endcan
                            
                            @can('deverify-users')
                                @if($user->phone_verified)
                                <x-tooltip text="Click to remove verification" position="top">
                                    <form action="{{ route('admin.users.deverify-phone', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-5 h-5 rounded transition flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </x-tooltip>
                                @endif
                            @endcan
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        @foreach($user->roles as $role)
                            <span class="px-2 py-1 text-xs font-medium border border-gray-300 dark:border-gray-700 rounded text-gray-900 dark:text-white mr-1">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        @if($user->managedBy)
                            <span class="text-xs">{{ $user->managedBy->name }}</span>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">No manager</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex gap-1.5 flex-wrap justify-end items-center">
                                    <!-- Edit Button -->
                                    @if(auth()->user()->hasPermissionTo('edit-users') || auth()->user()->hasRole('super-admin'))
                                        <x-tooltip text="Edit this user's information">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-7 h-7 rounded transition flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        </x-tooltip>
                                    @endif
                                    
                                    <!-- Block/Unblock Button -->
                                    @can('block-users')
                                        @if($user->id !== auth()->id())
                                        <x-tooltip :text="$user->isBlocked() ? 'Unblock this user - they will be able to access the system again' : 'Block this user - they will only be able to view their profile and logout'">
                                            <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-7 h-7 rounded transition flex items-center justify-center">
                                                    @if($user->isBlocked())
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                        </x-tooltip>
                                        @endif
                                    @endcan
                                    
                                    <!-- Delete Button -->
                                    @if(auth()->user()->hasPermissionTo('delete-users') || auth()->user()->hasRole('super-admin'))
                                        @if($user->id !== auth()->id())
                                        <x-tooltip text="Delete this user permanently">
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 w-7 h-7 rounded transition flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </x-tooltip>
                                        @endif
                                    @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $users->links() }}
    </div>
</div>

<!-- Impersonation Iframe Modal -->
<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50"
    aria-modal="true"
    role="dialog"
>
    <div class="absolute inset-0 bg-black/50" @click="close()"></div>

    <div class="absolute inset-0 p-3 sm:p-6 flex items-center justify-center">
        <div class="w-[95vw] sm:w-[90vw] max-w-6xl h-[85vh] max-h-[900px] bg-white dark:bg-gray-900 rounded-lg shadow-xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    Viewing as selected user
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.impersonate.stop') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                            Stop
                        </button>
                    </form>
                    <button type="button" class="text-sm px-3 py-1.5 rounded-lg bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100" @click="close()">
                        Close
                    </button>
                </div>
            </div>

            <div class="flex-1">
                <iframe
                    :src="iframeSrc"
                    class="w-full h-full"
                    style="border: 0"
                ></iframe>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
