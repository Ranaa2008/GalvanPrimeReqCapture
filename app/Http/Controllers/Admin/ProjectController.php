<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('view-projects')) {
            abort(403, 'You do not have permission to view projects.');
        }

        $projects = Project::with(['clients:id,name,email', 'developers:id,name,email', 'creator:id,name'])
            ->latest()
            ->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('create-projects')) {
            abort(403, 'You do not have permission to create projects.');
        }

        $clients = User::role('client')->orderBy('name')->get(['id', 'name', 'email']);
        $developers = User::role('developer')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.projects.create', compact('clients', 'developers'));
    }

    public function store(StoreProjectRequest $request)
    {
        $currentUser = auth()->user();

        DB::transaction(function () use ($request, $currentUser) {
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => $currentUser->id,
            ]);

            $this->syncMembers(
                $project,
                $request->input('client_ids', []),
                $request->input('developer_ids', []),
                $currentUser->id
            );
        });

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created and assignments saved successfully.');
    }

    public function edit(Project $project)
    {
        $user = auth()->user();

        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('edit-projects')) {
            abort(403, 'You do not have permission to edit projects.');
        }

        $project->load(['clients:id,name,email', 'developers:id,name,email']);

        $clients = User::role('client')->orderBy('name')->get(['id', 'name', 'email']);
        $developers = User::role('developer')->orderBy('name')->get(['id', 'name', 'email']);

        $selectedClientIds = $project->clients->pluck('id')->toArray();
        $selectedDeveloperIds = $project->developers->pluck('id')->toArray();

        return view('admin.projects.edit', compact(
            'project',
            'clients',
            'developers',
            'selectedClientIds',
            'selectedDeveloperIds'
        ));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $currentUser = auth()->user();

        DB::transaction(function () use ($request, $project, $currentUser) {
            $project->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $this->syncMembers(
                $project,
                $request->input('client_ids', []),
                $request->input('developer_ids', []),
                $currentUser->id
            );
        });

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();

        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('delete-projects')) {
            abort(403, 'You do not have permission to delete projects.');
        }

        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    private function syncMembers(Project $project, array $clientIds, array $developerIds, int $assignedBy): void
    {
        $syncData = [];

        foreach ($clientIds as $clientId) {
            $syncData[$clientId] = [
                'assignment_role' => 'client',
                'assigned_by' => $assignedBy,
            ];
        }

        foreach ($developerIds as $developerId) {
            if (array_key_exists($developerId, $syncData)) {
                continue;
            }

            $syncData[$developerId] = [
                'assignment_role' => 'developer',
                'assigned_by' => $assignedBy,
            ];
        }

        $project->members()->sync($syncData);
    }
}
