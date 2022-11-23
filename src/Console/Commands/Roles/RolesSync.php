<?php

namespace KieranFYI\Roles\Console\Commands\Roles;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KieranFYI\Roles\Models\Roles\Role;

class RolesSync extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs config roles to the database';

    /**
     * @var Collection
     */
    private Collection $existingRoles;

    /**
     * @var array
     */
    private array $rolesToSync;

    /**
     * @var array
     */
    private array $defaults;

    /**
     * @var Collection
     */
    private Collection $permissions;
    /**
     * @var array
     */
    private array $policyTypes;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        if (!$this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->existingRoles = Role::get();
        $this->rolesToSync = config('roles.roles');
        $this->defaults = config('roles.defaults');
        $this->policyTypes = config('permissions.policies.types');

        $this->seedRoles();

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function seedRoles()
    {

        foreach ($this->rolesToSync as $roleSettings) {
            $roleSettings = array_merge($this->defaults, $roleSettings);

            $role = $this->existingRoles
                    ->firstWhere('name', $roleSettings['name'])
                ?? new Role([
                    'name' => $roleSettings['name']
                ]);

            $role
                ->fill([
                    'display_order' => data_get($roleSettings, 'display_order', 1),
                    'colour' => data_get($roleSettings, 'colour'),
                    'default' => data_get($roleSettings, 'default', false),
                ])
                ->save();

            $this->syncPermissions($role, $roleSettings['permissions'] ?? []);
        }
    }

    /**
     * @throws Exception
     */
    private function syncPermissions(Role $role, array $permissions)
    {
        if (empty($permissions)) {
            $role->permissions()->sync([]);
            return;
        }

        $permissionsToAdd = [];

        foreach ($permissions as $name) {
            if (!Str::contains($name, '*')) {
                $permissionsToAdd[] = $name;
                continue;
            }

            $name = trim(str_replace('*', '', $name));
            foreach ($this->policyTypes as $type) {
                $permissionsToAdd[] = $type . ' ' . $name;
            }
        }

        $permissionsToRemove = $role->permissions
            ->pluck('name')
            ->diff($permissionsToAdd);

        foreach ($permissionsToRemove as $name) {
            $role->removePermission($name);
        }

        foreach ($permissionsToAdd as $name) {
            $role->addPermission($name);
        }
    }
}