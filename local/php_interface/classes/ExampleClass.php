<?php
namespace MyCompany\Example;

use Bitrix\Main\Event;
use Exception;

class ExampleClass{
    public static function compatibleHandler(&$arFields)
    {
        //do something with $arFields

        //or if need to cancel event processing:
        //global $APPLICATION;
        //$APPLICATION->throwException("Cancelled");
        //return false;
    }

    public static function notCompatibleHandler(Event $event){
        //$params = $event->getParameters();
        //some code
    }
}