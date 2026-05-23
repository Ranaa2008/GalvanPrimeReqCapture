<?php

namespace App\Providers;

use App\Models\Requirement;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

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

        View::composer('components.layout.topbar', function ($view) {
            $count = 0;
            $link = route('dashboard');
            $user = auth()->user();

            if ($user && $user->hasPermissionTo('view-requirements')) {
                if ($user->hasRole('Developer')) {
                    $count = Requirement::where('status', 'Unread')
                        ->whereHas('project.developers', function ($query) use ($user) {
                            $query->where('users.id', $user->id);
                        })
                        ->count();
                    $link = route('requirements.unread');
                } elseif ($user->hasRole('Client')) {
                    $count = Requirement::where('client_id', $user->id)
                        ->whereNotNull('status_updated_at')
                        ->where(function ($query) {
                            $query->whereNull('status_seen_at')
                                ->orWhereColumn('status_seen_at', '<', 'status_updated_at');
                        })
                        ->count();
                    $link = route('requirements.my');
                }
            }

            $view->with('unreadRequirementCount', $count);
            $view->with('notificationLink', $link);
        });
    }
}
