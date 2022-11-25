<?php
namespace KieranFYI\Roles\Listeners;

use KieranFYI\Roles\Events\Register\RegisterPermissionEvent;
use KieranFYI\Roles\Policies\Permissions\PermissionPolicy;
use KieranFYI\Roles\Policies\Roles\RolePolicy;

class RegisterPermissionListener
{
    /**
     * Handle the event.
     *
     * @param RegisterPermissionEvent $event
     * @return array
     */
    public function handle(RegisterPermissionEvent $event): array
    {
        return [
            PermissionPolicy::class,
            RolePolicy::class,
        ];
    }
}