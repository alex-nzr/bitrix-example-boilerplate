<?php
namespace Vendor\Project\Basic\Internals\Control;

use Bitrix\Main\EventManager as BitrixEventManager;
use Vendor\Project\Basic\Service\Handler\Main\Page;

/**
 * Class EventManager
 * @package Vendor\Project\Basic\Internals\Control
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

    private static function addEventHandlersFromArray(array $events, $register = false)
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
                            GetModuleID(__FILE__),
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
                        GetModuleID(__FILE__),
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
            ],
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
                        'class'  => Page::class,
                        'method' => 'doSomething',
                        'sort'   => 100
                    ],
                ]
            ]
        ];
    }
}