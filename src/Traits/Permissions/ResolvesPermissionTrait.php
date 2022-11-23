<?php

namespace KieranFYI\Roles\Traits\Permissions;

use KieranFYI\Roles\Models\Permissions\Permission;

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

        return Permission::where('name', $permission)
            ->firstOrFail();
    }
}