@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Requirement Details</h1>
    <a href="{{ route('developer.requirements.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
        Back
    </a>
</div>

<div class="space-y-6 max-w-5xl">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $requirement->project->name ?? 'N/A' }}</p>
                @if($requirement->project && $requirement->project->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $requirement->project->description }}</p>
                @endif
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                <span class="inline-flex mt-1 px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ ucfirst($requirement->status) }}</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Submitted on {{ $requirement->created_at->format('Y-m-d H:i') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Last updated {{ $requirement->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</p>
        <div class="mt-2 space-y-1">
            <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $requirement->client->name ?? 'N/A' }}</p>
            @if($requirement->client)
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $requirement->client->email }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $requirement->client->phone_number }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Requirement Title</p>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mt-1">{{ $requirement->title }}</h2>

        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-6">Requirement Description</p>
        <div class="mt-2 text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line break-words" style="overflow-wrap:anywhere; word-break:break-word;">
            {{ $requirement->details }}
        </div>
    </div>
</div>
@endsection
