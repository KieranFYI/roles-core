<?php

namespace KieranFYI\Roles\Core\Console\Commands\Sync;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use KieranFYI\Roles\Core\Events\Register\RegisterRoleEvent;
use KieranFYI\Roles\Core\Models\Roles\Role;
use KieranFYI\Roles\Core\Services\Register\RegisterRole;
use TypeError;

class SyncRoles extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:roles {--force : Force the operation to run when in production}';

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
    private array $rolesToSync = [];

    /**
     * @var array
     */
    private array $defaults;

    /**
     * @var Collection
     */
    private Collection $permissions;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        if (
            !$this->confirmToProceed()
            || !$this->confirmToProceed('Permission Sync will also be forced')
        ) {
            return self::FAILURE;
        }

        $this->call(SyncPermissions::class, ['--force' => true]);

        $this->existingRoles = Role::get();
        $this->defaults = config('roles.defaults');

        /*
         * Send the global event to register other package roles
         */
        $this->info('Registering Roles');
        event(RegisterRoleEvent::class, [], false);
        $this->registerRoles();

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
            $this->info(($role->exists ? 'Updating' : 'Adding') . ' role: ' . $roleSettings['name']);

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
     * @return void
     */
    private function registerRoles(): void
    {
        foreach (RegisterRole::roles() as $role) {

            if ($role instanceof RegisterRole) {
                $this->info('Registering role: ' . $role->name());
                $this->rolesToSync[] = $role->toArray();
                continue;
            }

            throw new TypeError(self::class . '::handle(): ' . RegisterRoleEvent::class . ' return must be of type ' . RegisterRole::class);
        }
    }

    /**
     * @throws Exception
     */
    private function syncPermissions(Role $role, array $permissions)
    {
        if (empty($permissions)) {
            if ($role->permissions->isEmpty()) {
                $this->info('Removing all permissions: ' . $role->name);
                $role->permissions()->sync([]);
            }
            return;
        }

        $permissionsToRemove = $role->permissions
            ->pluck('name')
            ->diff($permissions);

        foreach ($permissionsToRemove as $name) {
            $this->info('Removing permission: ' . $name);
            if ($role->hasPermission($name)) {
                $role->removePermission($name);
            }
        }

        foreach ($permissions as $name) {
            $this->info('Adding permission: ' . $name);
            if (!$role->hasPermission($name)) {
                $role->addPermission($name);
            }
        }
    }
}