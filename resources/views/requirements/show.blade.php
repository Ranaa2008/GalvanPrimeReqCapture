@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-widest text-gray-500 dark:text-gray-400">Requirement</p>
            <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-white">
                {{ $requirement->project?->name ?? 'Project' }}
            </h1>
        </div>
        <span class="inline-flex items-center rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1 text-sm font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900">
            {{ $requirement->status }}
        </span>
    </div>
</div>

<div class="max-w-4xl">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[2fr_1fr]">
        <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Request Details</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Full requirement description for review.</p>
            </div>
            <div class="px-6 py-6">
                <p class="text-gray-900 dark:text-white leading-relaxed whitespace-pre-line">{{ $requirement->description }}</p>
                @if($requirement->voice_path)
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Voice Message</p>
                        <audio class="w-full" controls>
                            <source src="{{ route('requirements.voice', $requirement) }}" type="{{ $requirement->voice_mime ?? 'audio/mpeg' }}">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                @endif
                @if($requirement->audio_path)
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Attachment</p>
                        @if(\Illuminate\Support\Str::startsWith($requirement->audio_mime, 'image/'))
                            <img class="w-full rounded-lg border border-gray-200 dark:border-gray-700" src="{{ route('requirements.audio', $requirement) }}" alt="Attachment">
                        @elseif(\Illuminate\Support\Str::startsWith($requirement->audio_mime, 'video/'))
                            <video class="w-full" controls>
                                <source src="{{ route('requirements.audio', $requirement) }}" type="{{ $requirement->audio_mime ?? 'video/mp4' }}">
                                Your browser does not support the video element.
                            </video>
                        @else
                            <audio class="w-full" controls>
                                <source src="{{ route('requirements.audio', $requirement) }}" type="{{ $requirement->audio_mime ?? 'audio/mpeg' }}">
                                Your browser does not support the audio element.
                            </audio>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <aside class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wide">Meta</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-700 dark:text-gray-100">
                    <div>
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-300">Submitted</span>
                        <p class="font-medium">{{ $requirement->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-300">Updated</span>
                        <p class="font-medium">{{ $requirement->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</h3>
                <div class="mt-4 space-y-2">
                    @can('edit-requirements')
                        <a href="{{ route('requirements.edit', $requirement) }}" class="block w-full text-center px-4 py-2 rounded-lg bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                            Edit Requirement
                        </a>
                    @endcan
                    @can('delete-requirements')
                        <form action="{{ route('requirements.destroy', $requirement) }}" method="POST" onsubmit="return confirm('Delete this requirement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                                Delete Requirement
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
