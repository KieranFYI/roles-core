<?php
namespace KieranFYI\Roles\Core\Listeners;

use KieranFYI\Roles\Core\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Core\Policies\Roles\RolePolicy;
use KieranFYI\Roles\Core\Services\Register\RegisterPermission;

class RegisterPermissionListener
{
    /**
     * Handle the event.
     *
     * @return array
     */
    public function handle(): array
    {
        RegisterPermission::register(
            'Developer',
            'Allows viewing of the secret sauce',
            100,
            'Ranks'
        );

        return [
            PermissionPolicy::class,
            RolePolicy::class,
        ];
    }
}