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
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_attempts')->default(0)->after('otp_code');
        });

        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_attempts')->default(0)->after('otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->dropColumn('failed_attempts');
        });

        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->dropColumn('failed_attempts');
        });
    }
};
