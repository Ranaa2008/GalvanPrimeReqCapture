@extends('layouts.app')

@section('content')
<div class="mb-6" x-data="{ viewMode: 'list' }">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles Management</h1>
        <div class="flex gap-2">
            <!-- View Toggle -->
            <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white dark:bg-gray-700 shadow' : ''" class="px-3 py-1 text-sm rounded transition">
                    List View
                </button>
                <button @click="viewMode = 'matrix'" :class="viewMode === 'matrix' ? 'bg-white dark:bg-gray-700 shadow' : ''" class="px-3 py-1 text-sm rounded transition">
                    Matrix View
                </button>
            </div>
            
            @can('create-roles')
            <a href="{{ route('admin.roles.create') }}" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 font-medium py-2 px-4 rounded-lg transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Role
            </a>
            @endcan
        </div>
    </div>

<!-- List View -->
<div x-show="viewMode === 'list'" x-transition>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            @forelse($roles as $role)
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-data="{ expanded: false }">
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $role->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        <span class="px-3 py-1 inline-flex text-xs font-medium border border-gray-300 dark:border-gray-700 rounded">
                            {{ $role->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        <span class="px-2 py-1 inline-flex text-xs font-semibold 
                            {{ $role->superiority == 1 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 
                               ($role->superiority == 2 ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : 
                               'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200') }} 
                            rounded">
                            Level {{ $role->superiority }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                        <button @click="expanded = !expanded" class="text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded transition flex items-center gap-1">
                            <span>{{ $role->permissions->count() }} permissions</span>
                            <svg x-show="!expanded" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <svg x-show="expanded" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $role->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @can('edit-roles')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</a>
                        @endcan
                        
                        @can('delete-roles')
                            @if(!in_array($role->name, ['super-admin', 'admin', 'user']))
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                            </form>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-xs">
                                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Protected
                            </span>
                            @endif
                        @endcan
                        
                        @if(!auth()->user()->can('edit-roles') && !auth()->user()->can('delete-roles'))
                            <span class="text-gray-400 dark:text-gray-500 text-xs">View Only</span>
                        @endif
                    </td>
                </tr>
                <!-- Expandable Permissions Row -->
                <tr x-show="expanded" x-transition class="bg-gray-50 dark:bg-gray-900">
                    <td colspan="6" class="px-6 py-4">
                        <div class="flex items-start gap-2">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">Permissions:</span>
                            <div class="flex flex-wrap gap-2">
                                @forelse($role->permissions as $permission)
                                    <span class="px-2 py-1 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded border border-indigo-200 dark:border-indigo-800">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic">No permissions assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody class="bg-white dark:bg-gray-800">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No roles found</td>
                </tr>
            </tbody>
            @endforelse
        </table>
    </div>
</div>
</div>
<!-- End List View -->

<!-- Matrix View -->
<div x-show="viewMode === 'matrix'" x-transition class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-700">Permission</th>
                    @foreach($roles as $role)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex flex-col items-center gap-1">
                                <span>{{ $role->name }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded
                                    {{ $role->superiority == 1 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 
                                       ($role->superiority == 2 ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : 
                                       'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200') }}">
                                    L{{ $role->superiority }}
                                </span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @php
                    $allPermissions = \Spatie\Permission\Models\Permission::orderBy('name')->get();
                @endphp
                @foreach($allPermissions as $permission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 sticky left-0 bg-white dark:bg-gray-800">
                            <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded">{{ $permission->name }}</span>
                        </td>
                        @foreach($roles as $role)
                            <td class="px-4 py-3 text-center">
                                @if($role->permissions->contains($permission->id))
                                    <span class="text-green-600 dark:text-green-400">
                                        <svg class="w-5 h-5 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">
                                        <svg class="w-5 h-5 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
