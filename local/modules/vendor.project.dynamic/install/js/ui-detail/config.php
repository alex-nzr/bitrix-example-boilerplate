<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - config.php
 * 21.02.2023 00:08
 * ==================================================
 */

use Vendor\Project\Dynamic\Entity\Dynamic;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Container;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

try
{
    $settings = [
        'moduleId' => ServiceManager::getModuleId(),
        'typeId'   => Dynamic::getInstance()->getTypeId(),
        'isAdmin'  => Container::getInstance()->getContext()->isCurrentUserAdmin()
    ];

    $item = Container::getInstance()->getContext()->getItem();
    if (!empty($item))
    {
        $settings['isNew'] = $item->isNew();
        $settings['entityTypeId'] = $item->getEntityTypeId();
        $settings['entityId'] = $item->getId();
    }
    else
    {
        throw new Exception('Item not found in context');
    }
}
catch(Exception $e)
{
    $settings = [
        'error' => $e->getMessage()
    ];
}

return [
    'css' => 'dist/index.bundle.css',
    'js' => 'dist/index.bundle.js',
    'rel' => [
		'main.core',
	],
    'skip_core' => false,
    'settings'  => $settings,
    'lang' => ['lang/ru/js_lang.php'],
];