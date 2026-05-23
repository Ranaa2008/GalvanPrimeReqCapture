@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Status: {{ $project->status }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Project Description</h2>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $project->description }}</p>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Clients: {{ $project->clients->pluck('name')->join(', ') }}
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requirement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Update</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($project->requirements as $requirement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $requirement->client?->name ?? 'Client' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ \Illuminate\Support\Str::limit($requirement->description, 60) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $requirement->status }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('requirements.show', $requirement) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    View
                                </a>
                                <form action="{{ route('requirements.status', $requirement) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ $requirement->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1 text-sm bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded">
                                        Save
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No requirements submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
