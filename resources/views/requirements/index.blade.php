@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Requirements</h1>
    @can('create-requirements')
        <a href="{{ route('requirements.create') }}" class="bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 font-medium py-2 px-4 rounded-lg transition">
            Create Requirement
        </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requirement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requirements as $requirement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $requirement->project?->name ?? 'Project' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ \Illuminate\Support\Str::limit($requirement->description, 60) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <span>{{ $requirement->status }}</span>
                                @if($requirement->status_updated_at && (!$requirement->status_seen_at || $requirement->status_seen_at->lt($requirement->status_updated_at)))
                                    <span class="inline-flex items-center rounded-full bg-black text-white border border-blue-200 dark:bg-black dark:text-white dark:border-gray-700 px-2 py-0.5 text-xs font-semibold">New</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('requirements.show', $requirement) }}" class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                @can('edit-requirements')
                                    <a href="{{ route('requirements.edit', $requirement) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</a>
                                @endcan
                                @can('delete-requirements')
                                    <form action="{{ route('requirements.destroy', $requirement) }}" method="POST" onsubmit="return confirm('Delete this requirement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No requirements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
