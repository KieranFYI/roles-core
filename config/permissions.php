<?php

return [
    'defaults' => [
        'description' => '',
        'power' => 0,
        'group' => null,
    ],

    'policies' => [
        'generate' => true,

        'types' => [
            'View Any', 'View', 'Create', 'Update', 'Delete', 'Restore', 'Force Delete'
        ],
    ],

    'permissions' => [
        [
            'name' => 'Administrator',
            'description' => 'Provide Administrator functionality',
            'power' => 99,
            'group' => 'Ranks',
        ],
        [
            'name' => 'Developer',
            'description' => 'Allows viewing of the secret sauce',
            'power' => 100,
            'group' => 'Ranks',
        ],
    ]
];