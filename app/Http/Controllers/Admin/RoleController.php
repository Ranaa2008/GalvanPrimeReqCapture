<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-roles');
        
        // Get all roles for matrix view (we need all at once for comparison)
        $roles = Role::with('permissions')->orderBy('superiority')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-roles');
        
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-roles');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'superiority' => 'nullable|integer|min:2|max:99',
        ]);

        // Only super-admin can set superiority levels
        if (!auth()->user()->hasRole('super-admin')) {
            unset($validated['superiority']);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'superiority' => $validated['superiority'] ?? 99
        ]);
        
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->authorize('view-roles');
        
        $role->load('permissions');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorize('edit-roles');
        
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('edit-roles');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'superiority' => 'nullable|integer|min:1|max:99',
        ]);

        // Only super-admin can change superiority levels
        if (!auth()->user()->hasRole('super-admin')) {
            // Non super-admin cannot change superiority
            unset($validated['superiority']);
        }

        $updateData = ['name' => $validated['name']];
        if (isset($validated['superiority'])) {
            $updateData['superiority'] = $validated['superiority'];
        }

        $role->update($updateData);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete-roles');
        
        // Protect system roles from deletion
        $protectedRoles = ['super-admin', 'admin', 'user'];
        if (in_array($role->name, $protectedRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete system role: ' . $role->name . '. This role is protected.');
        }

        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
