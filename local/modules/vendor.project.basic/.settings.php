<?php

use Vendor\Project\Basic\Service\Container;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Vendor\\Project\\Basic\\Controller',
        ],
        'readonly' => true,
    ],
    'services' => [
        'vendor.project.basic.service.container' => [
            'className' => Container::class
        ]
    ],
];