@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Requirement</h1>
</div>

<div class="max-w-4xl">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <form action="{{ route('client.requirements.update', $requirement) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project *</label>
                <select name="project_id" id="project_id" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $requirement->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requirement Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $requirement->title) }}" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="details" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requirement Details *</label>
                <textarea name="details" id="details" rows="8" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('details') border-red-500 @enderror">{{ old('details', $requirement->details) }}</textarea>
                @error('details')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('client.requirements.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">Update Requirement</button>
            </div>
        </form>
    </div>
</div>
@endsection
