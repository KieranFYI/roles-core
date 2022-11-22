<?php

namespace KieranFYI\Roles\Providers;

use Illuminate\Support\ServiceProvider;
use KieranFYI\Roles\Console\Commands\PermissionsSync;
use KieranFYI\Roles\Console\Commands\RolesSync;

class RolesPackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $root = __DIR__ . '/..';

        $this->publishes([
            $root . '/config/roles.php' => config_path('roles.php'),
            $root . '/config/permissions.php' => config_path('permissions.php')
        ], 'roles-config');

        $this->loadMigrationsFrom($root . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PermissionsSync::class,
                RolesSync::class,
            ]);
        }
    }
}
