<?php

use KieranFYI\Roles\Core\Services\Register\RegisterPermission;

return [
    'defaults' => [
        'description' => '',
        'power' => 0,
        'group' => null,
    ],

    'permissions' => [
        RegisterPermission::register(
            'Administrator',
            'Provide Administrator functionality',
            99,
            'Ranks'
        ),
        RegisterPermission::register(
            'Developer',
            'Allows viewing of the secret sauce',
            100,
            'Ranks'
        ),
    ]
];