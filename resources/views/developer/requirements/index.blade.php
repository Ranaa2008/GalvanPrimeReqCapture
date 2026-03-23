@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Assigned Client Requirements</h1>
</div>

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Requirement</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requirements as $requirement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $requirement->project->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            {{ $requirement->client->name ?? 'N/A' }}
                            @if($requirement->client)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $requirement->client->email }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $requirement->title }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ \Illuminate\Support\Str::limit($requirement->details, 140) }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ ucfirst($requirement->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $requirement->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('developer.requirements.show', $requirement) }}" class="inline-flex px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No assigned client requirements yet.</td>
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
