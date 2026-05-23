@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Projects</h1>
</div>

<div class="max-w-4xl">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
        <form action="{{ route('projects.store') }}" method="POST" class="p-6">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Description *</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror" required>{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Developers *</label>
                    <div class="max-h-56 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                        @forelse($developers as $developer)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-100 mb-2">
                                <input type="checkbox" name="developers[]" value="{{ $developer->id }}"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                    {{ in_array($developer->id, old('developers', [])) ? 'checked' : '' }}>
                                <span>{{ $developer->name }} ({{ $developer->email }})</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-300">No developers found.</p>
                        @endforelse
                    </div>
                    @error('developers')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('developers.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Clients *</label>
                    <div class="max-h-56 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                        @forelse($clients as $client)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-100 mb-2">
                                <input type="checkbox" name="clients[]" value="{{ $client->id }}"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                    {{ in_array($client->id, old('clients', [])) ? 'checked' : '' }}>
                                <span>{{ $client->name }} ({{ $client->email }})</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-300">No clients found.</p>
                        @endforelse
                    </div>
                    @error('clients')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('clients.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
