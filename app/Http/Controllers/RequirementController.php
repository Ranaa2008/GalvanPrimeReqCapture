<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Requirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RequirementController extends Controller
{
    private array $requirementStatuses = [
        'Unread',
        'Read',
        'On-Progress',
        'Completed',
    ];

    public function index(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view requirements.');
        }

        $requirementsQuery = Requirement::with('project');
        if (!$user->hasRole('super-admin')) {
            $requirementsQuery->where('client_id', $user->id);
        }

        $requirements = $requirementsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requirements.index', [
            'requirements' => $requirements,
        ]);
    }

    public function unread(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view requirements.');
        }

        if (!$user->hasRole('Developer')) {
            abort(403, 'This page is only for developers.');
        }

        $requirements = Requirement::with(['project', 'client'])
            ->where('status', 'Unread')
            ->whereHas('project.developers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requirements.unread', [
            'requirements' => $requirements,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('create-requirements')) {
            abort(403, 'You do not have permission to create requirements.');
        }

        $projects = $user->clientProjects()->orderBy('name')->get();

        return view('requirements.create', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('create-requirements')) {
            abort(403, 'You do not have permission to create requirements.');
        }

        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'description' => 'nullable|string',
            'voice' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/webm|max:10240',
            'attachment' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/webm,video/ogg,video/webm,video/mp4,video/quicktime,image/jpeg,image/png,image/webp,image/gif|max:10240',
        ]);

        if (empty($validated['description']) && !$request->hasFile('voice') && !$request->hasFile('attachment')) {
            return back()->withInput()->with('error', 'Please add text, a voice message, or an attachment.');
        }

        $allowedProjectIds = $user->clientProjects()->pluck('projects.id')->all();
        if (!in_array($validated['project_id'], $allowedProjectIds)) {
            return back()->withInput()->with('error', 'You are not assigned to the selected project.');
        }

        $audioPath = null;
        $audioMime = null;
        $voicePath = null;
        $voiceMime = null;
        if ($request->hasFile('voice')) {
            $voicePath = $request->file('voice')->store('requirements', 'public');
            $voiceMime = $request->file('voice')->getClientMimeType();
        }
        if ($request->hasFile('attachment')) {
            $audioPath = $request->file('attachment')->store('requirements', 'public');
            $audioMime = $request->file('attachment')->getClientMimeType();
        }

        Requirement::create([
            'project_id' => $validated['project_id'],
            'client_id' => $user->id,
            'description' => $validated['description'],
            'audio_path' => $audioPath,
            'audio_mime' => $audioMime,
            'voice_path' => $voicePath,
            'voice_mime' => $voiceMime,
            'status' => 'Unread',
            'status_updated_at' => null,
            'status_updated_by' => null,
            'status_seen_at' => null,
        ]);

        return redirect()->route('requirements.my')
            ->with('success', 'Requirement created successfully.');
    }

    public function show(Requirement $requirement): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view requirements.');
        }

        $isClientOwner = $requirement->client_id === $user->id;
        $isAssignedDeveloper = $requirement->project
            ->developers()
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->hasRole('super-admin') && !$isClientOwner && !$isAssignedDeveloper) {
            abort(403, 'You do not have access to this requirement.');
        }

        $requirement->load('project');

        if ($isClientOwner && $requirement->status_updated_at) {
            if (!$requirement->status_seen_at || $requirement->status_seen_at->lt($requirement->status_updated_at)) {
                $requirement->update(['status_seen_at' => now()]);
            }
        }

        return view('requirements.show', [
            'requirement' => $requirement,
            'canUpdateStatus' => $isAssignedDeveloper,
            'statuses' => $this->requirementStatuses,
        ]);
    }

    public function audio(Requirement $requirement)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view requirements.');
        }

        $isClientOwner = $requirement->client_id === $user->id;
        $isAssignedDeveloper = $requirement->project
            ->developers()
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->hasRole('super-admin') && !$isClientOwner && !$isAssignedDeveloper) {
            abort(403, 'You do not have access to this requirement.');
        }

        if (!$requirement->audio_path) {
            abort(404, 'No attachment available.');
        }

        $mimeType = $requirement->audio_mime ?: 'audio/mpeg';

        return Storage::disk('public')->response($requirement->audio_path, null, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function voice(Requirement $requirement)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view requirements.');
        }

        $isClientOwner = $requirement->client_id === $user->id;
        $isAssignedDeveloper = $requirement->project
            ->developers()
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->hasRole('super-admin') && !$isClientOwner && !$isAssignedDeveloper) {
            abort(403, 'You do not have access to this requirement.');
        }

        if (!$requirement->voice_path) {
            abort(404, 'No voice message available.');
        }

        $mimeType = $requirement->voice_mime ?: 'audio/mpeg';

        return Storage::disk('public')->response($requirement->voice_path, null, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function edit(Requirement $requirement): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('edit-requirements')) {
            abort(403, 'You do not have permission to edit requirements.');
        }

        if (!$user->hasRole('super-admin') && $requirement->client_id !== $user->id) {
            abort(403, 'You do not have access to this requirement.');
        }

        $projects = $user->hasRole('super-admin')
            ? Project::orderBy('name')->get()
            : $user->clientProjects()->orderBy('name')->get();

        return view('requirements.edit', [
            'requirement' => $requirement,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, Requirement $requirement): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('edit-requirements')) {
            abort(403, 'You do not have permission to edit requirements.');
        }

        if (!$user->hasRole('super-admin') && $requirement->client_id !== $user->id) {
            abort(403, 'You do not have access to this requirement.');
        }

        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'description' => 'nullable|string',
            'voice' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/webm|max:10240',
            'attachment' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/webm,video/ogg,video/webm,video/mp4,video/quicktime,image/jpeg,image/png,image/webp,image/gif|max:10240',
            'remove_attachment' => 'nullable|boolean',
            'remove_voice' => 'nullable|boolean',
        ]);

        $removeAttachment = !empty($validated['remove_attachment']);
        $removeVoice = !empty($validated['remove_voice']);
        $hasAttachment = $request->hasFile('attachment') || ($requirement->audio_path && !$removeAttachment);
        $hasVoice = $request->hasFile('voice') || ($requirement->voice_path && !$removeVoice);

        if (empty($validated['description']) && !$hasAttachment && !$hasVoice) {
            return back()->withInput()->with('error', 'Please add text, a voice message, or an attachment.');
        }

        if (!$user->hasRole('super-admin')) {
            $allowedProjectIds = $user->clientProjects()->pluck('projects.id')->all();
            if (!in_array($validated['project_id'], $allowedProjectIds)) {
                return back()->withInput()->with('error', 'You are not assigned to the selected project.');
            }
        }

        if ($removeAttachment && $requirement->audio_path) {
            Storage::disk('public')->delete($requirement->audio_path);
            $requirement->audio_path = null;
            $requirement->audio_mime = null;
        }

        if ($removeVoice && $requirement->voice_path) {
            Storage::disk('public')->delete($requirement->voice_path);
            $requirement->voice_path = null;
            $requirement->voice_mime = null;
        }

        if ($request->hasFile('attachment')) {
            if ($requirement->audio_path) {
                Storage::disk('public')->delete($requirement->audio_path);
            }

            $requirement->audio_path = $request->file('attachment')->store('requirements', 'public');
            $requirement->audio_mime = $request->file('attachment')->getClientMimeType();
        }

        if ($request->hasFile('voice')) {
            if ($requirement->voice_path) {
                Storage::disk('public')->delete($requirement->voice_path);
            }

            $requirement->voice_path = $request->file('voice')->store('requirements', 'public');
            $requirement->voice_mime = $request->file('voice')->getClientMimeType();
        }

        $requirement->update([
            'project_id' => $validated['project_id'],
            'description' => $validated['description'],
            'audio_path' => $requirement->audio_path,
            'audio_mime' => $requirement->audio_mime,
            'voice_path' => $requirement->voice_path,
            'voice_mime' => $requirement->voice_mime,
            'status' => 'Unread',
            'status_updated_at' => null,
            'status_updated_by' => null,
            'status_seen_at' => null,
        ]);

        return redirect()->route('requirements.my')
            ->with('success', 'Requirement updated successfully.');
    }

    public function destroy(Requirement $requirement): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('delete-requirements')) {
            abort(403, 'You do not have permission to delete requirements.');
        }

        if (!$user->hasRole('super-admin') && $requirement->client_id !== $user->id) {
            abort(403, 'You do not have access to this requirement.');
        }

        $requirement->delete();

        return redirect()->route('requirements.my')
            ->with('success', 'Requirement deleted successfully.');
    }

    public function updateStatus(Request $request, Requirement $requirement): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to update requirement status.');
        }

        $isAssignedDeveloper = $requirement->project
            ->developers()
            ->where('users.id', $user->id)
            ->exists();

        if (!$isAssignedDeveloper) {
            abort(403, 'You are not assigned to this project.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', $this->requirementStatuses),
        ]);

        $requirement->update([
            'status' => $validated['status'],
            'status_updated_at' => now(),
            'status_updated_by' => $user->id,
        ]);

        return back()->with('success', 'Requirement status updated.');
    }
}
