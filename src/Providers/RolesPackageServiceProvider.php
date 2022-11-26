<?php

namespace KieranFYI\Roles\Providers;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use KieranFYI\Roles\Console\Commands\Sync\SyncPermissions;
use KieranFYI\Roles\Console\Commands\Sync\SyncRoles;
use KieranFYI\Roles\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Listeners\RegisterPermissionListener;
use KieranFYI\Roles\Models\Permissions\Permission;
use KieranFYI\Roles\Models\Roles\Role;
use KieranFYI\Roles\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Policies\Roles\RolePolicy;
use KieranFYI\Roles\Traits\Policies\RegistersPoliciesTrait;

class RolesPackageServiceProvider extends ServiceProvider
{
    use RegistersPoliciesTrait;

    /**
     * @var array
     */
    protected array $policies = [
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class
    ];

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $root = __DIR__ . '/../..';

        $this->publishes([
            $root . '/config/roles.php' => config_path('roles.php'),
            $root . '/config/permissions.php' => config_path('permissions.php')
        ], ['roles', 'roles-config']);

        $this->mergeConfigFrom($root . '/config/roles.php', 'roles');
        $this->mergeConfigFrom($root . '/config/permissions.php', 'permissions');

        $this->loadMigrationsFrom($root . '/database/migrations');

        $this->registerPolicies();

        Gate::guessPolicyNamesUsing(function ($modelClass) {
            $modelClass = Str::replace('App\\Models', 'App\\Policies', $modelClass) . 'Policy';
            if (!class_exists($modelClass)) {
                throw new Exception('Unable to find Policy: ' . $modelClass);
            }
            return $modelClass;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPermissions::class,
                SyncRoles::class,
            ]);

            Event::listen(RegisterPermissionEvent::class, RegisterPermissionListener::class);
        }
    }
}
