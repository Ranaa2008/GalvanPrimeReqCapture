<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add composite and covering indexes for improved query performance
     */
    public function up(): void
    {
        // Check and add users table indexes
        $userIndexes = DB::select("SHOW INDEX FROM users");
        $existingUserIndexes = array_column($userIndexes, 'Key_name');
        
        Schema::table('users', function (Blueprint $table) use ($existingUserIndexes) {
            // Composite index for common queries filtering by blocked_at and managed_by
            // (Note: Individual indexes already exist from previous migration)
            if (!in_array('users_blocked_managed_idx', $existingUserIndexes)) {
                $table->index(['blocked_at', 'managed_by'], 'users_blocked_managed_idx');
            }
            
            // Index for email/phone verified columns (often filtered)
            if (!in_array('users_email_verified_idx', $existingUserIndexes)) {
                $table->index('email_verified', 'users_email_verified_idx');
            }
            if (!in_array('users_phone_verified_idx', $existingUserIndexes)) {
                $table->index('phone_verified', 'users_phone_verified_idx');
            }
            
            // Composite index for search queries (name, username, email)
            if (!in_array('users_name_email_idx', $existingUserIndexes)) {
                $table->index(['name', 'email'], 'users_name_email_idx');
            }
        });

        // Add index to model_has_roles for faster role lookups
        $roleIndexes = DB::select("SHOW INDEX FROM model_has_roles");
        $existingRoleIndexes = array_column($roleIndexes, 'Key_name');
        
        if (!in_array('model_has_roles_model_id_model_type_idx', $existingRoleIndexes)) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check which indexes exist before dropping
        $userIndexes = DB::select("SHOW INDEX FROM users");
        $existingUserIndexes = array_column($userIndexes, 'Key_name');
        
        Schema::table('users', function (Blueprint $table) use ($existingUserIndexes) {
            if (in_array('users_blocked_managed_idx', $existingUserIndexes)) {
                $table->dropIndex('users_blocked_managed_idx');
            }
            if (in_array('users_email_verified_idx', $existingUserIndexes)) {
                $table->dropIndex('users_email_verified_idx');
            }
            if (in_array('users_phone_verified_idx', $existingUserIndexes)) {
                $table->dropIndex('users_phone_verified_idx');
            }
            if (in_array('users_name_email_idx', $existingUserIndexes)) {
                $table->dropIndex('users_name_email_idx');
            }
        });

        // Check if index exists before dropping
        $roleIndexes = DB::select("SHOW INDEX FROM model_has_roles");
        $existingRoleIndexes = array_column($roleIndexes, 'Key_name');
        
        if (in_array('model_has_roles_model_id_model_type_idx', $existingRoleIndexes)) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex('model_has_roles_model_id_model_type_idx');
            });
        }
    }
};
