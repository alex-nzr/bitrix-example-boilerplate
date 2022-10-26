<?php
namespace MyCompany\ComposerExample;

use Bitrix\Main\EventManager as BitrixEventManager;

class EventManager{
    protected static array $events = [
        [
            'module'     => 'iblock',
            'eventType'  => 'OnBeforeIBlockElementAdd',
            'handler'    => [ExampleClass::class, "compatibleHandler"],
            'compatible' => "Y"
        ],
        [
            'module'     => 'sale',
            'eventType'  => 'OnSaleOrderEntitySaved',
            'handler'    => [ExampleClass::class, "notCompatibleHandler"],
            'compatible' => "N"
        ],
    ];
    public static function bindEvents():void
    {
        foreach (self::$events as $event)
        {
            if ($event['compatible'] === "Y"){
                BitrixEventManager::getInstance()->addEventHandlerCompatible(
                    $event['module'],
                    $event['eventType'],
                    $event['handler']
                );
            }
            else{
                BitrixEventManager::getInstance()->addEventHandler(
                    $event['module'],
                    $event['eventType'],
                    $event['handler']
                );
            }
        }
    }
}