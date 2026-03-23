@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Project</h1>
</div>

<div class="max-w-4xl">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.projects.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="client_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Clients *</label>
                <select name="client_ids[]" id="client_ids" multiple size="6"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('client_ids') border-red-500 @enderror">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(in_array($client->id, old('client_ids', [])))>
                            {{ $client->name }} ({{ $client->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple clients.</p>
                @error('client_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('client_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="developer_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Developers *</label>
                <select name="developer_ids[]" id="developer_ids" multiple size="6"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('developer_ids') border-red-500 @enderror">
                    @foreach($developers as $developer)
                        <option value="{{ $developer->id }}" @selected(in_array($developer->id, old('developer_ids', [])))>
                            {{ $developer->name }} ({{ $developer->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple developers.</p>
                @error('developer_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('developer_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.projects.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection
