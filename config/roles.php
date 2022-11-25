<?php

use KieranFYI\Roles\Services\Register\RegisterRole;

return [
    'defaults' => [
        'display_order' => 1,
        'colour' => '#8c8c8c',
        'default' => false
    ],

    'roles' => [
        RegisterRole::register('User')
        ->default(),

        RegisterRole::register('Administrator')
            ->displayOrder(99)
            ->colour('#F1B828')
            ->permission('Administrator'),

        RegisterRole::register('Developer')
            ->displayOrder(100)
            ->colour('#3498DB')
            ->permission('Developer'),
    ]
];