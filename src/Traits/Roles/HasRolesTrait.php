<?php

namespace KieranFYI\Roles\Core\Traits\Roles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Roles\Core\Models\Permissions\Permission;
use KieranFYI\Roles\Core\Models\Roles\Role;
use KieranFYI\Roles\Core\Traits\Permissions\ResolvesPermissionTrait;

/**
 * @property Collection $roles
 *
 * @mixin Model
 */
trait HasRolesTrait
{
    use ResolvesRoleTrait;
    use ResolvesPermissionTrait;
    use LoggingTrait;

    /**
     * @var Role
     */
    private static Role $guestRole;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    public static function bootHasRolesTrait()
    {
        self::$guestRole = new Role([
            'name' => 'Guest',
            'display_order' => 1,
            'colour' => '#ffffff'
        ]);
    }

    /**
     * @return MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'model', 'role_links')
            ->withTimestamps();
    }

    /**
     * @return Role
     */
    public function getPrimaryRoleAttribute(): Role
    {
        return $this->roles
            ->sortByDesc('display_order')
            ->whenEmpty(function ($collection) {
                return $collection->push(self::$guestRole);
            })
            ->first();
    }

    /**
     * @return Role
     */
    public function getPowerRoleAttribute(): Role
    {
        return $this->roles
            ->sortByDesc('power')
            ->whenEmpty(function ($collection) {
                return $collection->push(self::$guestRole);
            })
            ->first();
    }

    /**
     * @return int
     */
    public function getPowerAttribute(): int
    {
        return $this->power_role->power;
    }

    /**
     * @param Role|string $role
     * @return bool
     */
    public function hasRole(Role|string $role): bool
    {
        $role = $this->resolveRole($role);
        return $this->roles->contains('id', $role->id);
    }

    /**
     * @param Role|string $role
     */
    public function addRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);

        if ($this->hasRole($role)) {
            $this->security('Attempted to add role: ' . $role->name, $role);
        } else {
            $this->roles()->attach($role);
            $this->security('Added role: ' . $role->name, $role);
        }
    }

    /**
     * @param Role|string $role
     */
    public function removeRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);

        if ($this->hasRole($role)) {
            $this->roles()->detach($role);
            $this->security('Removed role: ' . $role->name, $role);
        } else {
            $this->security('Attempted to remove role: ' . $role->name, $role);
        }
    }

    /**
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermission(Permission|string $permission): bool
    {
        $permission = $this->resolvePermission($permission);
        return $this->roles
            ->pluck('permissions')
            ->flatten()
            ->contains('id', $permission->id);
    }

}