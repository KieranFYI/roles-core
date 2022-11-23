<?php

namespace KieranFYI\Roles\Traits\Permissions;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array $with
 *
 * @mixin Model
 */
class ForcePermissionsTrait
{
    public function initializeForcePermissionsTrait(): void
    {
        array_push($this->with, 'permissions');
    }
}