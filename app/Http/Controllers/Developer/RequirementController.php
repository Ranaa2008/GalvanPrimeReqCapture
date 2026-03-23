<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Requirement;

class RequirementController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        $requirements = Requirement::with(['project:id,name', 'client:id,name,email'])
            ->whereHas('project.developers', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->latest()
            ->paginate(10);

        return view('developer.requirements.index', compact('requirements'));
    }

    public function show(Requirement $requirement)
    {
        $currentUser = auth()->user();

        $isAssignedDeveloper = $requirement->project()
            ->whereHas('developers', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->exists();

        if (!$isAssignedDeveloper) {
            abort(403, 'You can only view requirements from projects assigned to you.');
        }

        $requirement->load(['project:id,name,description', 'client:id,name,email,phone_number']);

        return view('developer.requirements.show', compact('requirement'));
    }
}
