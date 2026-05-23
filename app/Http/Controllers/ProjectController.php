<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private array $projectStatuses = [
        'Active',
        'On-Progress',
        'Completed',
    ];

    public function index(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-projects')) {
            abort(403, 'You do not have permission to view projects.');
        }

        $projectsQuery = Project::query();
        if (!$user->hasRole('super-admin')) {
            $projectsQuery->where('created_by', $user->id);
        }

        $projects = $projectsQuery
            ->with(['developers', 'clients'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('create-projects')) {
            abort(403, 'You do not have permission to create projects.');
        }

        $developers = User::role('Developer')->orderBy('name')->get();
        $clients = User::role('Client')->orderBy('name')->get();

        return view('projects.create', [
            'developers' => $developers,
            'clients' => $clients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('create-projects')) {
            abort(403, 'You do not have permission to create projects.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'developers' => 'required|array|min:1',
            'developers.*' => 'integer|exists:users,id',
            'clients' => 'required|array|min:1',
            'clients.*' => 'integer|exists:users,id',
        ]);

        $developerIds = User::role('Developer')
            ->whereIn('id', $validated['developers'])
            ->pluck('id')
            ->all();

        $clientIds = User::role('Client')
            ->whereIn('id', $validated['clients'])
            ->pluck('id')
            ->all();

        if (count($developerIds) !== count($validated['developers'])) {
            return back()->withInput()->with('error', 'One or more selected developers are invalid.');
        }

        if (count($clientIds) !== count($validated['clients'])) {
            return back()->withInput()->with('error', 'One or more selected clients are invalid.');
        }

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => 'Active',
            'created_by' => $user->id,
        ]);

        $project->developers()->sync($developerIds);
        $project->clients()->sync($clientIds);

        return redirect()->route('projects.create')
            ->with('success', 'Project created and assigned successfully.');
    }

    public function myProjects(): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view projects.');
        }

        $projects = $user->developerProjects()
            ->with(['clients', 'requirements.client'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('projects.my', [
            'projects' => $projects,
        ]);
    }

    public function edit(Project $project): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('edit-projects')) {
            abort(403, 'You do not have permission to edit projects.');
        }

        if (!$user->hasRole('super-admin') && $project->created_by !== $user->id) {
            abort(403, 'You can only edit projects you created.');
        }

        $project->load(['developers', 'clients']);
        $developers = User::role('Developer')->orderBy('name')->get();
        $clients = User::role('Client')->orderBy('name')->get();

        return view('projects.edit', [
            'project' => $project,
            'developers' => $developers,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('edit-projects')) {
            abort(403, 'You do not have permission to edit projects.');
        }

        if (!$user->hasRole('super-admin') && $project->created_by !== $user->id) {
            abort(403, 'You can only edit projects you created.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'developers' => 'required|array|min:1',
            'developers.*' => 'integer|exists:users,id',
            'clients' => 'required|array|min:1',
            'clients.*' => 'integer|exists:users,id',
        ]);

        $developerIds = User::role('Developer')
            ->whereIn('id', $validated['developers'])
            ->pluck('id')
            ->all();

        $clientIds = User::role('Client')
            ->whereIn('id', $validated['clients'])
            ->pluck('id')
            ->all();

        if (count($developerIds) !== count($validated['developers'])) {
            return back()->withInput()->with('error', 'One or more selected developers are invalid.');
        }

        if (count($clientIds) !== count($validated['clients'])) {
            return back()->withInput()->with('error', 'One or more selected clients are invalid.');
        }

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $project->developers()->sync($developerIds);
        $project->clients()->sync($clientIds);

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('delete-projects')) {
            abort(403, 'You do not have permission to delete projects.');
        }

        if (!$user->hasRole('super-admin') && $project->created_by !== $user->id) {
            abort(403, 'You can only delete projects you created.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function show(Project $project): View
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('view-requirements')) {
            abort(403, 'You do not have permission to view project requirements.');
        }

        if (!$user->hasRole('super-admin')) {
            $isAssigned = $user->developerProjects()
                ->where('projects.id', $project->id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You are not assigned to this project.');
            }
        }

        $project->load(['clients', 'requirements.client']);

        return view('projects.show', [
            'project' => $project,
            'statuses' => ['Read', 'On-Progress', 'Completed'],
        ]);
    }
}
