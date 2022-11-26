<?php
namespace KieranFYI\Roles\Listeners;

use KieranFYI\Roles\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Policies\Roles\RolePolicy;

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