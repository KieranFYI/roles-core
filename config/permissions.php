<?php

use KieranFYI\Roles\Core\Services\Register\RegisterPermission;

return [
    'defaults' => [
        'description' => '',
        'power' => 0,
        'group' => null,
    ],
    'endpoint' => env('SERVICE_PERMISSIONS_ENDPOINT')
];