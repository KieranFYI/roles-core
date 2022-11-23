<?php

namespace KieranFYI\Roles\Traits\Roles;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array $with
 *
 * @mixin Model
 */
class ForceRolesTrait
{
    public function initializeForcePermissionsTrait(): void
    {
        array_push($this->with, 'roles', 'roles.permissions');
    }
}