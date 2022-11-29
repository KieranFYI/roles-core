<?php

namespace KieranFYI\Roles\Core\Traits\Roles;

use KieranFYI\Roles\Core\Models\Roles\Role;

trait ResolvesRoleTrait
{
    /**
     * @param Role|string $role
     * @return Role
     */
    private function resolveRole(Role|string $role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        return Role::where('name', $role)
            ->firstOrFail();
    }
}