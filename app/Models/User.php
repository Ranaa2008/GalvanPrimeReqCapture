<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ManagementTreeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The "booted" method of the model.
     * Automatically invalidate cache when managed_by changes
     */
    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if ($user->isDirty('managed_by')) {
                $treeService = app(ManagementTreeService::class);
                $treeService->invalidateCache($user->id);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'phone_number_secondary',
        'address',
        'avatar_url',
        'avatar_public_id',
        'password',
        'email_verified',
        'phone_verified',
        'blocked_at',
        'managed_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'blocked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's role superiority level (lowest number = highest authority)
     */
    public function getSuperiority(): int
    {
        $role = $this->roles()->orderBy('superiority')->first();
        return $role ? $role->superiority : 99;
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(): bool
    {
        return !is_null($this->blocked_at);
    }

    /**
     * Get the manager (super admin or system owner) managing this user
     */
    public function managedBy()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    /**
     * Get users managed by this user
     */
    public function managedUsers()
    {
        return $this->hasMany(User::class, 'managed_by');
    }

    /**
     * Check if current user can manage target user (dynamic, no hardcoded roles)
     */
    public function canManage(User $targetUser): bool
    {
        // Super admin can manage everyone
        if ($this->hasRole('super-admin')) {
            return true;
        }

        // Check if target is in this user's management tree
        $managementTreeIds = $this->getManagementTreeIds();
        
        if (!in_array($targetUser->id, $managementTreeIds)) {
            return false;
        }

        // Check superiority levels (lower number = higher authority)
        $mySuperiority = $this->getSuperiority();
        $targetSuperiority = $targetUser->getSuperiority();

        return $mySuperiority < $targetSuperiority;
    }

    /**
     * Get all user IDs in this user's management tree (recursive)
     */
    private function getManagementTreeIds(): array
    {
        $ids = [$this->id]; // Include self
        
        // Get direct reports
        $directReports = User::where('managed_by', $this->id)->pluck('id')->toArray();
        
        foreach ($directReports as $reportId) {
            $subordinate = User::find($reportId);
            if ($subordinate) {
                // Recursively get their subordinates
                $ids = array_merge($ids, $subordinate->getManagementTreeIds());
            }
        }
        
        return array_unique($ids);
    }

    /**
     * Check if user can assign a specific role (dynamic based on superiority)
     */
    public function canAssignRole($roleId): bool
    {
        $role = \Spatie\Permission\Models\Role::find($roleId);
        if (!$role) return false;

        // Super admin can assign any role
        if ($this->hasRole('super-admin')) {
            return true;
        }

        // Can only assign roles with higher superiority number (lower authority) than own level
        $mySuperiority = $this->getSuperiority();
        
        return isset($role->superiority) && $role->superiority > $mySuperiority;
    }

    /**
     * Projects where this user is assigned as either client or developer.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot(['assignment_role', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Projects where this user is assigned as client.
     */
    public function clientProjects()
    {
        return $this->projects()->wherePivot('assignment_role', 'client');
    }

    /**
     * Projects where this user is assigned as developer.
     */
    public function developerProjects()
    {
        return $this->projects()->wherePivot('assignment_role', 'developer');
    }

    /**
     * Requirements submitted by this user in client role.
     */
    public function submittedRequirements()
    {
        return $this->hasMany(Requirement::class, 'client_id');
    }
}
