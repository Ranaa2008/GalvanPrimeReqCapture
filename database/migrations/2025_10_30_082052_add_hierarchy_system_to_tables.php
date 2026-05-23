<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add superiority level to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('superiority')->default(99)->after('guard_name')
                ->comment('Role hierarchy level: 1=Super Admin, 2=System Owner, 3+=Sub roles');
            $table->index('superiority');
        });

        // Add blocked_at and managed_by to users table
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('remember_token')
                ->comment('When user was blocked');
            $table->foreignId('managed_by')->nullable()->after('blocked_at')
                ->constrained('users')->nullOnDelete()
                ->comment('Super admin or system owner managing this user');
            
            $table->index('blocked_at');
            $table->index('managed_by');
        });

        // Add assignable flag to role_has_permissions pivot
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->boolean('assignable')->default(false)->after('permission_id')
                ->comment('Can this role holder assign this permission to their sub-roles/users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropColumn('assignable');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['managed_by']);
            $table->dropColumn(['blocked_at', 'managed_by']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('superiority');
        });
    }
};
