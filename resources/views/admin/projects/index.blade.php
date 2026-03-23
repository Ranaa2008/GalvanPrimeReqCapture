@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projects</h1>
    @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasPermissionTo('create-projects'))
        <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
            Create Project
        </a>
    @endif
</div>

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Clients</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Developers</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Created By</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <td class="px-4 py-3 align-top">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $project->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $project->description ?: 'No description' }}</p>
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($project->clients->isEmpty())
                                <span class="text-sm text-gray-500 dark:text-gray-400">No clients</span>
                            @else
                                <div class="space-y-1">
                                    @foreach($project->clients as $client)
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $client->name }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            @if($project->developers->isEmpty())
                                <span class="text-sm text-gray-500 dark:text-gray-400">No developers</span>
                            @else
                                <div class="space-y-1">
                                    @foreach($project->developers as $developer)
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $developer->name }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top text-sm text-gray-700 dark:text-gray-300">{{ $project->creator->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasPermissionTo('edit-projects'))
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">Edit</a>
                                @endif

                                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasPermissionTo('delete-projects'))
                                    <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md transition">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No projects found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        {{ $projects->links() }}
    </div>
</div>
@endsection
