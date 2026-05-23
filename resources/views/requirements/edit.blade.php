@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Requirement</h1>
</div>

<div class="max-w-3xl">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
        <form action="{{ route('requirements.update', $requirement) }}" method="POST" class="p-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project *</label>
                <select name="project_id" id="project_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ (string) old('project_id', $requirement->project_id) === (string) $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requirement</label>
                <textarea name="description" id="description" rows="5"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $requirement->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Update text, record a voice message, and/or upload an attachment.</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Voice Message (Optional)</label>
                @if($requirement->voice_path)
                    <div class="mb-4">
                        <audio class="w-full" controls>
                            <source src="{{ route('requirements.voice', $requirement) }}" type="{{ $requirement->voice_mime ?? 'audio/mpeg' }}">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" name="remove_voice" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                        Remove voice message
                    </label>
                @endif
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="button" id="record-start" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition">Start Recording</button>
                    <button type="button" id="record-stop" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition" disabled>Stop</button>
                    <button type="button" id="record-clear" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition" disabled>Clear</button>
                </div>
                <div class="mt-4">
                    <audio id="voice-preview" class="w-full hidden" controls></audio>
                </div>
                <input type="file" name="voice" id="voice" accept="audio/*" class="hidden" />
                @error('voice')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachment (Optional)</label>
                @if($requirement->audio_path)
                    <div class="mb-4">
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
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" name="remove_attachment" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                        Remove existing attachment
                    </label>
                @endif
                <div class="mt-4 space-y-3">
                    <audio id="attachment-audio-preview" class="w-full hidden" controls></audio>
                    <video id="attachment-video-preview" class="w-full hidden" controls></video>
                    <img id="attachment-image-preview" class="w-full hidden rounded-lg border border-gray-200 dark:border-gray-700" alt="Attachment preview">
                </div>
                <div class="mt-4">
                    <input type="file" name="attachment" id="attachment" accept="audio/*,video/*,image/*" class="block w-full text-sm text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-200 dark:file:bg-gray-600 file:text-gray-700 dark:file:text-gray-100" />
                </div>
                @error('attachment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('requirements.my') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const startBtn = document.getElementById('record-start');
    const stopBtn = document.getElementById('record-stop');
    const clearBtn = document.getElementById('record-clear');
    const voicePreview = document.getElementById('voice-preview');
    const voiceInput = document.getElementById('voice');
    const attachmentInput = document.getElementById('attachment');
    const attachmentAudioPreview = document.getElementById('attachment-audio-preview');
    const attachmentVideoPreview = document.getElementById('attachment-video-preview');
    const attachmentImagePreview = document.getElementById('attachment-image-preview');
    let recorder;
    let chunks = [];

    function resetAttachmentPreviews() {
        attachmentAudioPreview.classList.add('hidden');
        attachmentVideoPreview.classList.add('hidden');
        attachmentImagePreview.classList.add('hidden');
        attachmentAudioPreview.src = '';
        attachmentVideoPreview.src = '';
        attachmentImagePreview.src = '';
    }

    function resetVoicePreview() {
        voicePreview.classList.add('hidden');
        voicePreview.src = '';
    }

    function showAttachmentPreview(file) {
        resetAttachmentPreviews();
        if (!file) return;
        const url = URL.createObjectURL(file);
        if (file.type.startsWith('audio/')) {
            attachmentAudioPreview.src = url;
            attachmentAudioPreview.classList.remove('hidden');
        } else if (file.type.startsWith('video/')) {
            attachmentVideoPreview.src = url;
            attachmentVideoPreview.classList.remove('hidden');
        } else if (file.type.startsWith('image/')) {
            attachmentImagePreview.src = url;
            attachmentImagePreview.classList.remove('hidden');
        }
    }

    function setButtons(recording) {
        startBtn.disabled = recording;
        stopBtn.disabled = !recording;
        clearBtn.disabled = recording && !voicePreview.src;
    }

    startBtn.addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            recorder = new MediaRecorder(stream);
            chunks = [];
            recorder.ondataavailable = (event) => chunks.push(event.data);
            recorder.onstop = () => {
                const blob = new Blob(chunks, { type: recorder.mimeType || 'audio/webm' });
                const file = new File([blob], 'requirement.webm', { type: blob.type });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                voiceInput.files = dataTransfer.files;
                voicePreview.src = URL.createObjectURL(file);
                voicePreview.classList.remove('hidden');
                clearBtn.disabled = false;
                stream.getTracks().forEach(track => track.stop());
            };
            recorder.start();
            setButtons(true);
        } catch (error) {
            alert('Microphone access is required to record audio.');
        }
    });

    stopBtn.addEventListener('click', () => {
        if (recorder && recorder.state !== 'inactive') {
            recorder.stop();
            setButtons(false);
        }
    });

    clearBtn.addEventListener('click', () => {
        resetVoicePreview();
        voiceInput.value = '';
        clearBtn.disabled = true;
    });

    attachmentInput.addEventListener('change', () => {
        if (attachmentInput.files.length) {
            showAttachmentPreview(attachmentInput.files[0]);
        }
    });

    resetAttachmentPreviews();
    resetVoicePreview();
    setButtons(false);
</script>
@endsection
