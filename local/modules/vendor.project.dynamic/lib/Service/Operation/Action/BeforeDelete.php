<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - BeforeDelete.php
 * 03.03.2023 22:49
 * ==================================================
 */


namespace Vendor\Project\Dynamic\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Result;

/**
 * @class BeforeDelete
 * @package Vendor\Project\Dynamic\Service\Operation\Action
 */
class BeforeDelete extends Action
{
    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     */
    public function process(Item $item): Result
    {
        return new Result();
    }
}