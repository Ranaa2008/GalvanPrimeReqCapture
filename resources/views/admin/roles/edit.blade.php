@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Role: {{ $role->name }}</h1>
</div>

<div class="max-w-3xl">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
        
        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="Enter role name" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(auth()->user()->hasRole('super-admin'))
                <div>
                    <label for="superiority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Hierarchy Level (Superiority)
                    </label>
                    @if($role->superiority == 1)
                        <div class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg">
                            Level 1 (Super Admin - Cannot be changed)
                        </div>
                        <input type="hidden" name="superiority" value="1">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Level 1 is permanently reserved for Super Admin role
                        </p>
                    @else
                        <input type="number" name="superiority" id="superiority" value="{{ old('superiority', $role->superiority) }}" 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('superiority') border-red-500 @enderror"
                            placeholder="Enter level (2-99)" min="2" max="99" required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Lower number = Higher authority. Level 1 is reserved for Super Admin. Level 2 for System Owner.
                        </p>
                    @endif
                    @error('superiority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Hierarchy Level</label>
                    <div class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg">
                        Level {{ $role->superiority }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Only Super Admin can change hierarchy levels
                    </p>
                </div>
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Permissions</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                    @foreach($permissions as $permission)
                    <div class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                        <label for="permission_{{ $permission->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ $permission->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('permissions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.roles.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
