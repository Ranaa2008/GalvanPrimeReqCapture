<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    /**
     * Get all roles ordered by superiority
     * 
     * @return Collection
     */
    public function getAllOrderedBySuperiority(): Collection
    {
        return Role::orderBy('superiority')->get();
    }

    /**
     * Get roles by superiority comparison
     * 
     * @param int $superiority
     * @param string $operator Comparison operator (>, <, =, >=, <=)
     * @return Collection
     */
    public function getRolesBySuperiority(int $superiority, string $operator = '>'): Collection
    {
        return Role::where('superiority', $operator, $superiority)
            ->orderBy('superiority')
            ->get();
    }

    /**
     * Find role by ID
     * 
     * @param int $roleId
     * @return Role|null
     */
    public function find(int $roleId): ?Role
    {
        return Role::find($roleId);
    }

    /**
     * Get role with permissions
     * 
     * @param int $roleId
     * @return Role|null
     */
    public function findWithPermissions(int $roleId): ?Role
    {
        return Role::with('permissions')->find($roleId);
    }
}
