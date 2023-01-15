<?php

use KieranFYI\Roles\Core\Services\Register\RegisterRole;

return [
    'defaults' => [
        'display_order' => 1,
        'colour' => '#8c8c8c',
        'default' => false
    ],
    'endpoint' => env('SERVICE_ROLES_ENDPOINT')
];