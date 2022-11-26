<?php

namespace KieranFYI\Roles\Console\Commands\Sync;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use KieranFYI\Roles\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Models\Permissions\Permission;
use KieranFYI\Roles\Policies\AbstractPolicy;
use KieranFYI\Roles\Services\Register\RegisterPermission;
use SplFileInfo;
use TypeError;

class SyncPermissions extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs config permissions to the database';

    /**
     * @var Collection
     */
    private Collection $existingPermissions;

    /**
     * @var array
     */
    private array $defaults;

    /**
     * @var string[]
     */
    private array $permissionsToSync = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->existingPermissions = Permission::get();
        $this->defaults = config('permissions.defaults');

        /*
         * Send the global event to register other package permissions
         */
        $this->info('Registering Permissions');
        $results = event(RegisterPermissionEvent::class, [], false);
        $this->registerPermissions($results);

        $this->seedPolicies();
        $this->seedPermissions();

        return self::SUCCESS;
    }

    private function seedPermissions(): void
    {
        collect($this->permissionsToSync)
            ->filter()
            ->unique()
            ->each(function (array $permissionSettings) {
                $permissionSettings = array_merge($this->defaults, $permissionSettings);
                $permission = $this->existingPermissions
                        ->firstWhere('name', $permissionSettings['name'])
                    ?? new Permission([
                        'name' => $permissionSettings['name']
                    ]);

                $this->info(($permission->exists ? 'Updating' : 'Adding') . ' permission ' . $permissionSettings['name']);

                $permission
                    ->fill([
                        'description' => data_get($permissionSettings, 'description', ''),
                        'power' => data_get($permissionSettings, 'power', 0),
                        'group' => data_get($permissionSettings, 'group'),
                    ])
                    ->save();
            });
    }

    private function seedPolicies(): void
    {
        if (!is_dir(app_path('Policies'))) {
            return;
        }

        $policies = collect(File::allFiles(app_path('Policies')))
            ->map(function (SplFileInfo $file) {
                if (!str_contains($file->getBasename(), '.php')) {
                    return null;
                }
                return 'App' . str_replace(['.php', app_path()], '', $file->getRealPath());
            })
            ->filter();

        /** @var AbstractPolicy $policy */
        foreach ($policies as $policy) {
            if (!is_subclass_of($policy, AbstractPolicy::class)) {
                continue;
            }
            $policy = new $policy;
            $this->info('Registering App Policies: ' . $policy->policyName());
            $this->permissionsToSync = array_merge($this->permissionsToSync, $policy->permissions());
        }
    }

    private function registerPermissions(array $results): void
    {
        $permissions = array_merge(config('permissions.permissions', []), ...$results);
        foreach ($permissions as $permission) {

            if ($permission instanceof RegisterPermission) {
                $this->info('Registering Package Permission: ' . $permission->name());
                $this->permissionsToSync[] = $permission->toArray();
                continue;
            }

            if (is_subclass_of($permission, AbstractPolicy::class)) {
                /** @var AbstractPolicy $policy */
                $policy = new $permission;
                $this->info('Registering Package Policies: ' . $policy->policyName());
                $this->permissionsToSync = array_merge(
                    $this->permissionsToSync,
                    collect($policy->permissions())
                        ->pluck('name')
                        ->toArray()
                );
                continue;
            }

            throw new TypeError(self::class . '::handle(): ' . RegisterPermissionEvent::class . ' return must be of type ' . RegisterPermission::class . ' or ' . AbstractPolicy::class);
        }
    }
}