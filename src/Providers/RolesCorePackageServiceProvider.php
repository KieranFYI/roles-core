<?php

namespace KieranFYI\Roles\Core\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use KieranFYI\Misc\Http\Middleware\CacheableMiddleware;
use KieranFYI\Roles\Core\Console\Commands\Sync\SyncPermissions;
use KieranFYI\Roles\Core\Console\Commands\Sync\SyncRoles;
use KieranFYI\Roles\Core\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Core\Events\Register\RegisterRoleEvent;
use KieranFYI\Roles\Core\Http\Middleware\HasPermission;
use KieranFYI\Roles\Core\Listeners\RegisterPermissionListener;
use KieranFYI\Roles\Core\Listeners\RegisterRoleListener;
use KieranFYI\Roles\Core\Models\Permissions\Permission;
use KieranFYI\Roles\Core\Models\Roles\Role;
use KieranFYI\Roles\Core\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Core\Policies\Roles\RolePolicy;
use KieranFYI\Roles\Core\Traits\Policies\RegistersPoliciesTrait;
use KieranFYI\Roles\Core\Traits\Roles\HasRolesTrait;
use Symfony\Component\HttpFoundation\Response;

class RolesCorePackageServiceProvider extends ServiceProvider
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

        $router->aliasMiddleware('perm', HasPermission::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPermissions::class,
                SyncRoles::class,
            ]);

            Event::listen(RegisterPermissionEvent::class, RegisterPermissionListener::class);
            Event::listen(RegisterRoleEvent::class, RegisterRoleListener::class);
        } else {
            CacheableMiddleware::checking(function (Response $response) {
                $user = Auth::user();
                if (!is_a($user, Model::class, true) && !in_array(HasRolesTrait::class, class_uses_recursive($user))) {
                    return;
                }

                /** @var HasRolesTrait $user */
                if (!$user->relationLoaded('roles')) {
                    $user->load('roles', 'roles.permissions');
                }

                $updatedAt = null;

                $roleUpdatedAt = $user->roles->max('pivot.updated_at');
                if (!is_null($roleUpdatedAt)) {
                    app('misc-debugbar')->debug('Role last modified: ' . $roleUpdatedAt);
                    $updatedAt = $roleUpdatedAt;
                }

                $permissionUpdateAt = $user->roles
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('pivot.updated_at')
                    ->max();
                if (!is_null($permissionUpdateAt) && $permissionUpdateAt->greaterThan($updatedAt)) {
                    app('misc-debugbar')->debug('Permission last modified: ' . $permissionUpdateAt);
                    $updatedAt = $permissionUpdateAt;
                }

                if (is_null($updatedAt)) {
                    return;
                }

                $options = ['last_modified' => $updatedAt];
                $response->setCache($options);
            });
        }
    }
}
