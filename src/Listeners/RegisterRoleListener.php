<?php
namespace KieranFYI\Roles\Core\Listeners;

use KieranFYI\Roles\Core\Services\Register\RegisterRole;

class RegisterRoleListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(): void
    {
        RegisterRole::register('User')
            ->default();

        RegisterRole::register('Developer')
            ->displayOrder(100)
            ->colour('#3498DB')
            ->permission('Developer');
    }
}