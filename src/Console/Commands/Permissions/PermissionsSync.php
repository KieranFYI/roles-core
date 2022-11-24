<?php

namespace KieranFYI\Roles\Console\Commands\Permissions;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use KieranFYI\Roles\Events\RegisterPermissionEvent;
use KieranFYI\Roles\Models\Permissions\Permission;
use KieranFYI\Roles\Services\RegisterPermission;
use SplFileInfo;
use TypeError;

class PermissionsSync extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

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
     * @var array
     */
    private array $permissionsToSync;

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
        $this->permissionsToSync = config('permissions.permissions');


        // RegisterPermission
        $results = event(RegisterPermissionEvent::class, [], false);
        foreach ($results as $permission) {
            if (!($permission instanceof RegisterPermission)) {
                throw new TypeError(self::class . '::handle(): ' . RegisterPermissionEvent::class . ' return must be of type ' . RegisterPermission::class);
            }

            $this->permissionsToSync[] = $permission->toArray();
        }

        $this->seedPermissions();
        $this->seedPolicyPermissions();
        return self::SUCCESS;
    }

    private function seedPermissions(): void
    {
        foreach ($this->permissionsToSync as $permissionSettings) {
            $permissionSettings = array_merge($this->defaults, $permissionSettings);
            $perm = $this->existingPermissions
                    ->firstWhere('name', $permissionSettings['name'])
                ?? new Permission([
                    'name' => $permissionSettings['name']
                ]);

            $perm
                ->fill([
                    'description' => data_get($permissionSettings, 'description', ''),
                    'power' => data_get($permissionSettings, 'power', 0),
                    'group' => data_get($permissionSettings, 'group'),
                ])
                ->save();
        }
    }

    private function seedPolicyPermissions(): void
    {
        if (!config('permissions.policies.generate', false) || !is_dir(app_path('Policies'))) {
            return;
        }

        $existingPermissions = Permission::get();
        $policyTypes = config('permissions.policies.types');

        $policies = collect(File::allFiles(app_path('Policies')))
            ->map(function (SplFileInfo $file) {
                if (!str_contains($file->getBasename(), '.php') || str_contains($file->getBasename(), 'Abstract')) {
                    return null;
                }
                return trim(
                    ucwords(
                        implode(
                            ' ',
                            preg_split('/(?=[A-Z])/', str_replace(['.php', 'Policy'], '', $file->getBasename()))
                        )
                    )
                );
            })
            ->filter();

        foreach ($policies as $policy) {
            foreach ($policyTypes as $type) {
                $name = $type . ' ' . $policy;
                $perm = $existingPermissions->firstWhere('name', $name)
                    ?? new Permission([
                        'name' => $name,
                    ]);

                $perm
                    ->fill([
                        'description' => $type . (Str::contains($type, 'Any') ? ' ' : ' a ') . $policy,
                        'power' => data_get($this->defaults, 'power', 0),
                        'group' => $policy,
                    ])
                    ->save();
            }
        }
    }
}