<?php

namespace KieranFYI\Roles\Traits\Roles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use KieranFYI\Roles\Models\Permissions\Permission;
use KieranFYI\Roles\Models\Roles\Role;
use KieranFYI\Roles\Traits\Permissions\ResolvesPermissionTrait;

/**
 * @property Collection $roles
 *
 * @mixin Model
 */
class HasRolesTrait
{
    use ResolvesRoleTrait;
    use ResolvesPermissionTrait;

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
        return $this->morphToMany(Role::class, 'model', 'role_links');
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
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermission(Permission|string $permission): bool
    {
        $permission = $this->resolvePermission($permission);

        return $this->roles
            ->pluck('permissions.id')
            ->contains($permission->id);
    }

}