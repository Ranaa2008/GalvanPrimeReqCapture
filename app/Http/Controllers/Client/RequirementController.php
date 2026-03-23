<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequirementRequest;
use App\Http\Requests\UpdateRequirementRequest;
use App\Models\Project;
use App\Models\Requirement;

class RequirementController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        $requirements = Requirement::with(['project:id,name'])
            ->where('client_id', $currentUser->id)
            ->latest()
            ->paginate(10);

        return view('client.requirements.index', compact('requirements'));
    }

    public function create()
    {
        $currentUser = auth()->user();

        $projects = Project::whereHas('clients', function ($query) use ($currentUser) {
            $query->where('users.id', $currentUser->id);
        })->orderBy('name')->get(['id', 'name']);

        return view('client.requirements.create', compact('projects'));
    }

    public function store(StoreRequirementRequest $request)
    {
        $currentUser = auth()->user();

        $project = Project::where('id', $request->project_id)
            ->whereHas('clients', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->first();

        if (!$project) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You can only submit requirements for projects assigned to you.');
        }

        Requirement::create([
            'project_id' => $project->id,
            'client_id' => $currentUser->id,
            'title' => $request->title,
            'details' => $request->details,
            'status' => 'submitted',
        ]);

        return redirect()->route('client.requirements.index')
            ->with('success', 'Requirement submitted successfully.');
    }

    public function edit(Requirement $requirement)
    {
        $currentUser = auth()->user();

        if ($requirement->client_id !== $currentUser->id) {
            abort(403, 'You can only edit your own requirements.');
        }

        $projects = Project::whereHas('clients', function ($query) use ($currentUser) {
            $query->where('users.id', $currentUser->id);
        })->orderBy('name')->get(['id', 'name']);

        return view('client.requirements.edit', compact('requirement', 'projects'));
    }

    public function update(UpdateRequirementRequest $request, Requirement $requirement)
    {
        $currentUser = auth()->user();

        if ($requirement->client_id !== $currentUser->id) {
            abort(403, 'You can only update your own requirements.');
        }

        $project = Project::where('id', $request->project_id)
            ->whereHas('clients', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->first();

        if (!$project) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You can only assign requirements to projects assigned to you.');
        }

        $requirement->update([
            'project_id' => $project->id,
            'title' => $request->title,
            'details' => $request->details,
        ]);

        return redirect()->route('client.requirements.index')
            ->with('success', 'Requirement updated successfully.');
    }

    public function destroy(Requirement $requirement)
    {
        $currentUser = auth()->user();

        if ($requirement->client_id !== $currentUser->id) {
            abort(403, 'You can only delete your own requirements.');
        }

        $requirement->delete();

        return redirect()->route('client.requirements.index')
            ->with('success', 'Requirement deleted successfully.');
    }
}
