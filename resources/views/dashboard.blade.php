<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">Welcome, {{ auth()->user()->name }}!</h3>
                    <p class="text-gray-600 mb-6">You're successfully logged in to your account.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <p class="text-sm text-gray-600">Username</p>
                            <p class="text-lg font-semibold text-gray-800">{{ auth()->user()->username }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-lg font-semibold text-gray-800">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="text-lg font-semibold text-gray-800">{{ auth()->user()->phone_number }}</p>
                        </div>
                    </div>

                    @if(auth()->user()->hasAnyPermission(['view-users', 'view-roles', 'view-permissions']))
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-lg shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xl font-bold mb-2">Management Access</h4>
                                <p class="text-blue-100">You have management privileges. Access the admin panel to manage users and system.</p>
                            </div>
                            <a href="{{ route('admin.dashboard') }}" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-200">
                                Go to Admin Panel →
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="mt-6">
                        <h4 class="text-lg font-semibold mb-3">Your Roles & Permissions</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse(auth()->user()->roles as $role)
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-gray-500 text-sm">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
