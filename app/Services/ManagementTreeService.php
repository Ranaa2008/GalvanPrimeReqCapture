<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ManagementTreeService
{
    /**
     * Get all user IDs in the management tree using optimized SQL CTE
     * This replaces the recursive PHP method for better performance
     * 
     * @param int $userId The root user ID
     * @param bool $includeRoot Whether to include the root user in results
     * @return array Array of user IDs in the management tree
     */
    public function getManagementTreeIds(int $userId, bool $includeRoot = true): array
    {
        // Check database driver to use appropriate query method
        $driver = DB::connection()->getDriverName();
        
        // MySQL 8.0+ and PostgreSQL support WITH RECURSIVE
        // MariaDB 10.2+ also supports it, but syntax check might fail
        if ($driver === 'mysql' || $driver === 'pgsql') {
            try {
                $results = DB::select("
                    WITH RECURSIVE subordinates AS (
                        SELECT id, managed_by
                        FROM users
                        WHERE id = ?
                        
                        UNION ALL
                        
                        SELECT u.id, u.managed_by
                        FROM users u
                        INNER JOIN subordinates s ON u.managed_by = s.id
                    )
                    SELECT id FROM subordinates
                ", [$userId]);
                
                $ids = array_map(fn($row) => $row->id, $results);
                
                // Remove root user if not needed
                if (!$includeRoot) {
                    $ids = array_filter($ids, fn($id) => $id !== $userId);
                }
                
                return array_values($ids);
            } catch (\Exception $e) {
                // Fallback to PHP recursion if CTE fails
                return $this->getManagementTreeIdsRecursive($userId, $includeRoot);
            }
        }
        
        // Fallback to PHP recursion for other databases or if CTE fails
        return $this->getManagementTreeIdsRecursive($userId, $includeRoot);
    }
    
    /**
     * Fallback recursive PHP method for databases that don't support CTE
     * 
     * @param int $userId The root user ID
     * @param bool $includeRoot Whether to include the root user in results
     * @return array Array of user IDs in the management tree
     */
    private function getManagementTreeIdsRecursive(int $userId, bool $includeRoot = true): array
    {
        $ids = $includeRoot ? [$userId] : [];
        
        // Get direct reports
        $directReports = User::where('managed_by', $userId)->pluck('id')->toArray();
        
        foreach ($directReports as $reportId) {
            // Recursively get their subordinates
            $subordinateIds = $this->getManagementTreeIdsRecursive($reportId, true);
            $ids = array_merge($ids, $subordinateIds);
        }
        
        return array_unique($ids);
    }

    /**
     * Get cached management tree IDs with automatic invalidation
     * 
     * @param int $userId The root user ID
     * @param bool $includeRoot Whether to include the root user in results
     * @return array Array of user IDs in the management tree
     */
    public function getCachedManagementTreeIds(int $userId, bool $includeRoot = true): array
    {
        $cacheKey = "management_tree_{$userId}_" . ($includeRoot ? 'with' : 'without') . '_root';
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($userId, $includeRoot) {
            return $this->getManagementTreeIds($userId, $includeRoot);
        });
    }

    /**
     * Invalidate cache for a user and all their managers (upward in hierarchy)
     * Call this when user's managed_by changes
     * 
     * @param int $userId The user whose cache should be invalidated
     * @return void
     */
    public function invalidateCache(int $userId): void
    {
        // Clear cache for this user
        Cache::forget("management_tree_{$userId}_with_root");
        Cache::forget("management_tree_{$userId}_without_root");
        
        // Clear cache for all managers up the chain
        $user = User::find($userId);
        if ($user && $user->managed_by) {
            $this->invalidateCacheUpward($user->managed_by);
        }
    }

    /**
     * Recursively invalidate cache upward through management chain
     * 
     * @param int $managerId
     * @return void
     */
    private function invalidateCacheUpward(int $managerId): void
    {
        Cache::forget("management_tree_{$managerId}_with_root");
        Cache::forget("management_tree_{$managerId}_without_root");
        
        $manager = User::find($managerId);
        if ($manager && $manager->managed_by) {
            $this->invalidateCacheUpward($manager->managed_by);
        }
    }

    /**
     * Check if a user is in another user's management tree
     * 
     * @param int $managerId The manager user ID
     * @param int $targetUserId The target user ID to check
     * @return bool True if target is in manager's tree
     */
    public function isInManagementTree(int $managerId, int $targetUserId): bool
    {
        $treeIds = $this->getCachedManagementTreeIds($managerId);
        return in_array($targetUserId, $treeIds);
    }

    /**
     * Get direct reports (immediate subordinates) for a user
     * 
     * @param int $userId The manager user ID
     * @return \Illuminate\Support\Collection Collection of User models
     */
    public function getDirectReports(int $userId)
    {
        return User::where('managed_by', $userId)
            ->with(['roles'])
            ->get();
    }

    /**
     * Get all subordinates (recursive) as User models with relationships
     * 
     * @param int $userId The manager user ID
     * @param bool $includeRoot Whether to include the root user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getManagementTreeUsers(int $userId, bool $includeRoot = true)
    {
        $ids = $this->getCachedManagementTreeIds($userId, $includeRoot);
        
        return User::whereIn('id', $ids)
            ->with(['roles', 'managedBy'])
            ->get();
    }
}
