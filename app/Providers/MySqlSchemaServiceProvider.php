<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use App\Database\MariaDbConnection;

class MySqlSchemaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register custom MariaDB connection resolver early
        Connection::resolverFor('mariadb', function ($connection, $database, $prefix, $config) {
            return new MariaDbConnection($connection, $database, $prefix, $config);
        });
        
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MariaDbConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
