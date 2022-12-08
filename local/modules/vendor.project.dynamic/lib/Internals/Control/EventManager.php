<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - EventManager.php
 * 24.11.2022 12:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\Control;

use Bitrix\Main\EventManager as BitrixEventManager;
use Vendor\Project\Dynamic\Handler;

/**
 * Class EventManager
 * @package Vendor\Project\Dynamic\Internals\Control
 */
class EventManager
{
    public static function addBasicEventHandlers()
    {
        static::addEventHandlersFromArray(static::getBasicEvents(), true);
    }

    public static function addRuntimeEventHandlers()
    {
        static::addEventHandlersFromArray(static::getRunTimeEvents());
    }

    private static function addEventHandlersFromArray(array $events, bool $register = false)
    {
        foreach ($events as $moduleId => $event)
        {
            foreach ($event as $eventName => $handlers)
            {
                foreach ($handlers as $handler)
                {
                    if ($register)
                    {
                        BitrixEventManager::getInstance()->registerEventHandler(
                            $moduleId,
                            $eventName,
                            ServiceManager::getModuleId(),
                            $handler['class'],
                            $handler['method'],
                            $handler['sort'] ?? 100,
                        );
                    }
                    else
                    {
                        BitrixEventManager::getInstance()->addEventHandler(
                            $moduleId,
                            $eventName,
                            [$handler['class'], $handler['method']],
                            false,
                            $handler['sort'] ?? 100
                        );
                    }
                }
            }
        }
    }

    public static function removeBasicEventHandlers()
    {
        foreach (static::getBasicEvents() as $moduleId => $event)
        {
            foreach ($event as $eventName => $handlers)
            {
                foreach ($handlers as $handler)
                {
                    BitrixEventManager::getInstance()->unRegisterEventHandler(
                        $moduleId,
                        $eventName,
                        ServiceManager::getModuleId(),
                        $handler['class'],
                        $handler['method'],
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    private static function getBasicEvents(): array
    {
        return [
            'main' => [
                'onPageStart' => [
                    [
                        'class'  => self::class,
                        'method' => 'addRuntimeEventHandlers',
                        'sort'   => 100
                    ],
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private static function getRunTimeEvents(): array
    {
        return [
            'crm' => [
                'onEntityDetailsTabsInitialized' => [
                    [
                        'class'  => Handler\Crm::class,
                        'method' => 'changeDetailCardTabs',
                        'sort'   => 400
                    ],
                ],
            ],
        ];
    }
}