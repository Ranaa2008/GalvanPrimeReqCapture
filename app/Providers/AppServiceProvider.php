<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Fix for MySQL 5.7 and MariaDB compatibility - disable virtual columns check
        try {
            $version = DB::select('SELECT VERSION() as version')[0]->version ?? '';
            if (stripos($version, 'MariaDB') !== false || version_compare($version, '8.0', '<')) {
                // For older MySQL versions, we need to handle schema differently
                config(['database.connections.mysql.strict' => false]);
            }
        } catch (\Exception $e) {
            // Ignore if we can't check version
        }
    }
}
