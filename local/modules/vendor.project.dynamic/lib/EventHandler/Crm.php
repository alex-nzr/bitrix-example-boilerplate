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

namespace Vendor\Project\Dynamic\EventHandler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Container;

/**
 * @class Crm
 * @package Vendor\Project\Dynamic\EventHandler
 */
class Crm
{
    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\EventResult|null
     * @throws \Exception
     */
    public static function changeDetailCardTabs(Event $event): ?EventResult
    {
        if (Container::getInstance()->getRouter()->isInDynamicTypeSection())
        {
            $tabs = $event->getParameter('tabs');
            foreach ($tabs as $key => $tab)
            {
                //Some custom logic with detail card tabs
                if ($tab['id'] === 'someTabToDelete')
                {
                    unset($tabs[$key]);
                }
            }
            return new EventResult(EventResult::SUCCESS, [
                'tabs' => $tabs,
            ]);
        }
        return null;
    }
}