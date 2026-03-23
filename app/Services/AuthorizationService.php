<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class AuthorizationService
{
    /**
     * Get the highest superiority level for a user (lower number = higher rank)
     * 
     * @param User $user
     * @return int
     */
    public function getUserHighestSuperiority(User $user): int
    {
        $lowestSuperiority = 9999; // Start with a very high number
        
        foreach ($user->roles as $role) {
            $superiority = $role->superiority ?? 9999;
            if ($superiority < $lowestSuperiority) {
                $lowestSuperiority = $superiority;
            }
        }
        
        return $lowestSuperiority;
    }

    /**
     * Check if current user can manage target user
     * Uses the superiority column from roles table (dynamic hierarchy)
     * 
     * @param User $currentUser
     * @param User $targetUser
     * @param ManagementTreeService $treeService
     * @return bool
     */
    public function canManageUser(User $currentUser, User $targetUser, ManagementTreeService $treeService): bool
    {
        // Super admin can manage everyone
        if ($currentUser->hasRole('super-admin')) {
            return true;
        }

        // Check if target user is in current user's management tree
        if (!$treeService->isInManagementTree($currentUser->id, $targetUser->id)) {
            return false;
        }

        // Get the highest superiority level for both users (lower number = higher rank)
        $currentSuperiority = $this->getUserHighestSuperiority($currentUser);
        $targetSuperiority = $this->getUserHighestSuperiority($targetUser);

        // Can only manage users with HIGHER superiority number (lower rank)
        // Example: superiority 2 can manage superiority 3, 4, 5, etc.
        return $currentSuperiority < $targetSuperiority;
    }

    /**
     * Get roles that current user can assign
     * Returns roles with higher superiority number (lower rank) than current user
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignableRoles(User $user)
    {
        // Super admin can assign any role
        if ($user->hasRole('super-admin')) {
            return Role::orderBy('superiority')->get();
        }

        // Get current user's highest superiority (lowest number)
        $currentSuperiority = $this->getUserHighestSuperiority($user);

        // Filter roles that have HIGHER superiority number (lower rank)
        return Role::where('superiority', '>', $currentSuperiority)
                   ->orderBy('superiority')
                   ->get();
    }

    /**
     * Check if user can assign a specific role
     * 
     * @param User $user
     * @param int|Role $role
     * @return bool
     */
    public function canAssignRole(User $user, $role): bool
    {
        if (is_int($role)) {
            $role = Role::find($role);
        }

        if (!$role) {
            return false;
        }

        // Super admin can assign any role
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Can only assign roles with higher superiority number (lower authority)
        $mySuperiority = $this->getUserHighestSuperiority($user);
        
        return isset($role->superiority) && $role->superiority > $mySuperiority;
    }

    /**
     * Validate if user can assign requested roles
     * 
     * @param User $user
     * @param array $requestedRoleNames
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateRoleAssignment(User $user, array $requestedRoleNames): array
    {
        $assignableRoles = $this->getAssignableRoles($user)->pluck('name')->toArray();
        
        foreach ($requestedRoleNames as $roleName) {
            if (!in_array($roleName, $assignableRoles)) {
                return [
                    'valid' => false,
                    'error' => 'You do not have permission to assign the role: ' . $roleName
                ];
            }
        }
        
        return ['valid' => true, 'error' => null];
    }
}
