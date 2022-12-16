<?php

use Vendor\Project\Dynamic\Service\Integration\Intranet\CustomSectionProvider;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Vendor\\Project\\Dynamic\\Controller',
        ],
        'readonly' => true,
    ],
    'intranet.customSection' => [
        'value' => [
            'provider' => CustomSectionProvider::class,
        ],
    ],
];