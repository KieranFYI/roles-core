<?php

namespace KieranFYI\Roles\Core\Traits\Permissions;

use KieranFYI\Roles\Core\Models\Permissions\Permission;

trait ResolvesPermissionTrait
{
    /**
     * @param Permission|string $permission
     * @return Permission
     */
    private function resolvePermission(Permission|string $permission): Permission
    {
        if ($permission instanceof Permission) {
            return $permission;
        }

        if (!config()->has('permissions.cache')) {
            config(['permissions.cache' => Permission::get()]);
        }

        $model = config('permissions.cache')->where('name', $permission)
            ->first();
        if (is_null($model)) {
            return Permission::where('name', $permission)->firstOrFail();
        }

        return $model;
    }
}