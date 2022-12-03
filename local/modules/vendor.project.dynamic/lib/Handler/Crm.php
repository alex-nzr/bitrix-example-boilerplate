<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Crm.php
 * 24.11.2022 18:49
 * ==================================================
 */


namespace Vendor\Project\Dynamic\Handler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;

/**
 * Class Crm
 * @package Vendor\Project\Dynamic\Handler
 */
class Crm
{
    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\EventResult
     * @throws \Exception
     */
    public static function changeDetailCardTabs(Event $event): EventResult
    {
        $tabs = $event->getParameter('tabs');
        if (ServiceManager::getInstance()->isInDynamicTypeSection())
        {
            foreach ($tabs as $key => $tab)
            {
                //Some custom logic with detail card tabs
                if ($tab['id'] === 'someTabToDelete')
                {
                    unset($tabs[$key]);
                }
            }
        }
        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}