<?php

namespace KieranFYI\Roles\Core\Traits\Roles;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array $with
 *
 * @mixin Model
 */
trait ForceRolesTrait
{
    public function initializeForceRolesTrait(): void
    {
        array_push($this->with, 'roles', 'roles.permissions');
    }
}