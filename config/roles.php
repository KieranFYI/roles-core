<?php

return [
    'defaults' => [
        'display_order' => 1,
        'colour' => '#8c8c8c',
        'default' => false
    ],

    'roles' => [
        [
            'name' => 'User',
            'default' => true,
            'permissions' => []
        ],
        [
            'name' => 'Administrator',
            'display_order' => 99,
            'colour' => '#F1B828',
            'permissions' => [
                'Administrator'
            ]
        ],
        [
            'name' => 'Developer',
            'display_order' => 100,
            'colour' => '#3498DB',
            'permissions' => [
                'Developer',
            ]
        ],
    ]
];