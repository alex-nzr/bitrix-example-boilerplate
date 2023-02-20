<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - BeforeAdd.php
 * 19.02.2023 01:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service\Operation\Action;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Result;

class BeforeAdd extends Action
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