<?php

use KieranFYI\Roles\Core\Services\Register\RegisterRole;

return [
    'defaults' => [
        'display_order' => 1,
        'colour' => '#8c8c8c',
        'default' => false
    ],

    'roles' => [
        RegisterRole::register('User')
        ->default(),
        RegisterRole::register('Developer')
            ->displayOrder(100)
            ->colour('#3498DB')
            ->permission('Developer'),
    ]
];