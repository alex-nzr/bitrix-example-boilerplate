<?php
namespace MyCompany\Example;

use \Bitrix\Main\EventManager;

class EventCollector{
    protected static array $events = [
        [
            'module'     => 'iblock',
            'eventType'  => 'OnBeforeIBlockElementAdd',
            'handler'    => ["\MyCompany\Example\ExampleClass", "compatibleHandler"],
            'compatible' => "Y"
        ],
        [
            'module'     => 'iblock',
            'eventType'  => 'OnBeforeIBlockElementUpdate',
            'handler'    => ["\MyCompany\Example\ExampleClass", "notCompatibleHandler"],
            'compatible' => "N"
        ],
    ];
    public static function bindEvents():void
    {
        foreach (self::$events as $event)
        {
            if ($event['compatible'] === "Y"){
                EventManager::getInstance()->addEventHandlerCompatible(
                    $event['module'],
                    $event['eventType'],
                    $event['handler']
                );
            }
            else{
                EventManager::getInstance()->addEventHandler(
                    $event['module'],
                    $event['eventType'],
                    $event['handler']
                );
            }
        }
    }
}