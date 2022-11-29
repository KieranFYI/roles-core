<?php
namespace KieranFYI\Roles\Core\Listeners;

use KieranFYI\Roles\Core\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Core\Policies\Roles\RolePolicy;

class RegisterPermissionsListener
{
    /**
     * Handle the event.
     *
     * @return array
     */
    public function handle(): array
    {
        return [
            PermissionPolicy::class,
            RolePolicy::class,
        ];
    }
}