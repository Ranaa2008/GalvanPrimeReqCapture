<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Get paginated users with relationships and optional filters
     * 
     * @param array $filters ['search' => 'query', 'user_ids' => [1,2,3]]
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['roles', 'managedBy']);
        
        // Filter by user IDs (for management tree)
        if (!empty($filters['user_ids'])) {
            $query->whereIn('id', $filters['user_ids']);
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Find user by ID with relationships
     * 
     * @param int $userId
     * @return User|null
     */
    public function findWithRelations(int $userId): ?User
    {
        return User::with(['roles.permissions', 'managedBy'])->find($userId);
    }

    /**
     * Get users by role superiority with eager loading
     * 
     * @param int $superiority Get users with roles having this superiority level
     * @param string $operator Comparison operator (<, >, =, <=, >=)
     * @param array $excludeIds User IDs to exclude
     * @return Collection
     */
    public function getUsersByRoleSuperiority(int $superiority, string $operator = '<', array $excludeIds = []): Collection
    {
        $query = User::whereHas('roles', function($q) use ($superiority, $operator) {
            $q->where('superiority', $operator, $superiority);
        })->with(['roles']);
        
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        
        return $query->get();
    }

    /**
     * Get available managers for a given role
     * 
     * @param int $roleSuperiority The superiority level of the role
     * @param array $allowedUserIds Limit to these user IDs (for management tree)
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableManagers(int $roleSuperiority, array $allowedUserIds = [])
    {
        $query = User::whereHas('roles', function($q) use ($roleSuperiority) {
            $q->where('superiority', '<', $roleSuperiority);
        });
        
        if (!empty($allowedUserIds)) {
            $query->whereIn('id', $allowedUserIds);
        }
        
        return $query->with('roles')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->join(', '),
                    'superiority' => $user->roles->min('superiority')
                ];
            })
            ->sortBy('superiority')
            ->values();
    }

    /**
     * Create a new user
     * 
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user
     * 
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete a user
     * 
     * @param User $user
     * @return bool|null
     */
    public function delete(User $user): ?bool
    {
        return $user->delete();
    }

    /**
     * Sync user roles
     * 
     * @param User $user
     * @param array $roles
     * @return void
     */
    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    /**
     * Get users managed by a specific user
     * 
     * @param int $managerId
     * @return Collection
     */
    public function getDirectReports(int $managerId): Collection
    {
        return User::where('managed_by', $managerId)
            ->with(['roles'])
            ->get();
    }

    /**
     * Block a user
     * 
     * @param User $user
     * @param int $blockedBy User ID who blocked them
     * @return bool
     */
    public function block(User $user, int $blockedBy): bool
    {
        return $user->update([
            'blocked_at' => now(),
            'managed_by' => $blockedBy
        ]);
    }

    /**
     * Unblock a user
     * 
     * @param User $user
     * @return bool
     */
    public function unblock(User $user): bool
    {
        return $user->update(['blocked_at' => null]);
    }

    /**
     * Verify user's email
     * 
     * @param User $user
     * @return bool
     */
    public function verifyEmail(User $user): bool
    {
        return $user->update(['email_verified' => true]);
    }

    /**
     * Deverify user's email
     * 
     * @param User $user
     * @return bool
     */
    public function deverifyEmail(User $user): bool
    {
        return $user->update(['email_verified' => false]);
    }

    /**
     * Verify user's phone
     * 
     * @param User $user
     * @return bool
     */
    public function verifyPhone(User $user): bool
    {
        return $user->update(['phone_verified' => true]);
    }

    /**
     * Deverify user's phone
     * 
     * @param User $user
     * @return bool
     */
    public function deverifyPhone(User $user): bool
    {
        return $user->update(['phone_verified' => false]);
    }
}
