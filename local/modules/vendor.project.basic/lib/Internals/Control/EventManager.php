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
namespace Vendor\Project\Basic\Internals\Control;

use Bitrix\Main\EventManager as BitrixEventManager;
use Vendor\Project\Basic\EventHandler;

/**
 * Class EventManager
 * @package Vendor\Project\Basic\Internals\Control
 */
class EventManager
{
    /**
     * @return void
     */
    public static function addBasicEventHandlers(): void
    {
        static::addEventHandlersFromArray(static::getBasicEvents(), true);
    }

    /**
     * @return void
     */
    public static function addRuntimeEventHandlers(): void
    {
        static::addEventHandlersFromArray(static::getRunTimeEvents());
    }

    /**
     * @param array $events
     * @param bool $register
     * @return void
     */
    private static function addEventHandlersFromArray(array $events, bool $register = false): void
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

    /**
     * @return void
     */
    public static function removeBasicEventHandlers(): void
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
            'main' => [
                'onPageStart' => [
                    [
                        'class'  => EventHandler\Main::class,
                        'method' => 'onPageStartHandler',
                        'sort'   => 400
                    ],
                ],
            ],
        ];
    }
}