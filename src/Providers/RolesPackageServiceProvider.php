<?php

namespace KieranFYI\Roles\Core\Providers;

use Exception;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use KieranFYI\Roles\Core\Console\Commands\Sync\SyncPermissions;
use KieranFYI\Roles\Core\Console\Commands\Sync\SyncRoles;
use KieranFYI\Roles\Core\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Core\Http\Middleware\HasPermission;
use KieranFYI\Roles\Core\Listeners\RegisterPermissionsListener;
use KieranFYI\Roles\Core\Models\Permissions\Permission;
use KieranFYI\Roles\Core\Models\Roles\Role;
use KieranFYI\Roles\Core\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Core\Policies\Roles\RolePolicy;
use KieranFYI\Roles\Core\Traits\Policies\RegistersPoliciesTrait;

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
    public function boot(Router $router)
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
            $modelClass = Str::replace('App\\Models', 'App\\Policies', $modelClass);
            if (!class_exists($modelClass)) {
                if (!Str::endsWith($modelClass, 'Policy')) {
                    $modelClass .= 'Policy';
                }
                if (!class_exists($modelClass)) {
                    throw new Exception('Unable to find Policy: ' . $modelClass);
                }
            }
            return $modelClass;
        });

        $router->aliasMiddleware('perm', HasPermission::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPermissions::class,
                SyncRoles::class,
            ]);

            Event::listen(RegisterPermissionEvent::class, RegisterPermissionsListener::class);
        }
    }
}
