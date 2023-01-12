<?php

namespace KieranFYI\Roles\Core\Console\Commands\Sync;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use KieranFYI\Roles\Core\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Core\Models\Permissions\Permission;
use KieranFYI\Roles\Core\Policies\AbstractPolicy;
use KieranFYI\Roles\Core\Services\Register\RegisterPermission;
use SplFileInfo;

class SyncPermissions extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permissions {--force : Force the operation to run when in production}';

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
        event(RegisterPermissionEvent::class, [], false);

        foreach (RegisterPermission::permissions() as $permission) {
                $this->info('Registering Package Permission: ' . $permission->name());
                $this->permissionsToSync[] = $permission->toArray();
        }

        foreach (RegisterPermission::policies() as $policy) {
            /** @var AbstractPolicy $policy */
            $policy = new $policy;
            $this->info('Registering Package Policies: ' . $policy->policyName());
            $this->permissionsToSync = array_merge(
                $this->permissionsToSync,
                $policy->permissions()
            );
        }


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

                $class = Str::of($file->getRealPath())
                    ->replace(['.php', app_path()], '')
                    ->replace('/', '\\')
                    ->toString();
                return 'App' . $class;
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
}
