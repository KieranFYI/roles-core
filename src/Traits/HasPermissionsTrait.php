<?php

namespace KieranFYI\Roles\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Roles\Models\Permissions\Permission;

/**
 * @property Collection $permissions
 * @property integer $power
 *
 * @mixin Model
 */
trait HasPermissionsTrait
{
    use ResolvesPermissionTrait;
    use LoggingTrait;

    /**
     * @return int
     */
    public function getPowerAttribute(): int
    {
        $permission = $this->permissions
            ->sortBy('power', SORT_NUMERIC, true)
            ->first();
        if (is_null($permission)) {
            return 0;
        }

        return $permission->power;
    }

    /**
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'permission_links');
    }

    /**
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermission(Permission|string $permission): bool
    {
        $permission = $this->resolvePermission($permission);

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * @param Permission|string $permission
     */
    public function addPermission(Permission|string $permission): void
    {
        $permission = $this->resolvePermission($permission);

        if ($this->hasPermission($permission)) {
            $this->security('Attempted to add permission: ' . $permission->name, $permission);
        } else {
            $this->permissions()->attach($permission);
            $this->security('Added permission: ' . $permission->name, $permission);
        }
    }

    /**
     * @param Permission|string $permission
     */
    public function removePermission(Permission|string $permission): void
    {
        $permission = $this->resolvePermission($permission);

        if ($this->hasPermission($permission)) {
            $this->permissions()->detach($permission);
            $this->security('Removed permission: ' . $permission->name, $permission);
        } else {
            $this->security('Attempted to remove permission: ' . $permission->name, $permission);
        }
    }
}