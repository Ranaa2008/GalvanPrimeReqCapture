@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Requirements</h1>
    <a href="{{ route('client.requirements.create') }}" class="px-4 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
        Submit Requirement
    </a>
</div>

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requirements as $requirement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $requirement->project->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $requirement->title }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ \Illuminate\Support\Str::limit($requirement->details, 120) }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ ucfirst($requirement->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $requirement->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('edit-own-requirements')
                                    <a href="{{ route('client.requirements.edit', $requirement) }}" class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">Edit</a>
                                @endcan

                                @can('delete-own-requirements')
                                    <form action="{{ route('client.requirements.destroy', $requirement) }}" method="POST" onsubmit="return confirm('Delete this requirement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md transition">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No requirements submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        {{ $requirements->links() }}
    </div>
</div>
@endsection
