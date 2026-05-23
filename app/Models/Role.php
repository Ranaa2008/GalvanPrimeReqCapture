<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'superiority',
    ];

    /**
     * Check if this role can manage another role
     */
    public function canManage(Role $targetRole): bool
    {
        // Cannot manage same level
        if ($this->superiority === $targetRole->superiority) {
            return false;
        }

        // Can only manage lower levels (higher numbers)
        return $this->superiority < $targetRole->superiority;
    }

    /**
     * Get roles that this role can manage
     */
    public function getManagedRoles()
    {
        return static::where('superiority', '>', $this->superiority)->get();
    }

    /**
     * Scope to get roles below a certain superiority level
     */
    public function scopeBelowLevel($query, int $level)
    {
        return $query->where('superiority', '>', $level);
    }

    /**
     * Scope to get roles at or below a certain superiority level
     */
    public function scopeAtOrBelowLevel($query, int $level)
    {
        return $query->where('superiority', '>=', $level);
    }
}
