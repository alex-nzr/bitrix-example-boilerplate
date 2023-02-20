<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - Context.php
 * 19.02.2023 21:49
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service;

use Bitrix\Crm\Item;
use Bitrix\Main\Engine\CurrentUser;
use CUser;

/**
 * @class Context
 * @package Vendor\Project\Dynamic\Service
 */
class Context extends \Bitrix\Crm\Service\Context
{
    private ?Item        $item = null;
    private ?CurrentUser $user = null;

    public function __construct(array $params = [])
    {
        $this->setCurrentUser();
        parent::__construct($params);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return void
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @return \Bitrix\Crm\Item|null
     */
    public function getItem(): ?Item
    {
        return $this->item;
    }

    /**
     * @return bool
     */
    public function isCurrentUserAdmin(): bool
    {
        return !empty($this->user) && $this->user->isAdmin();
    }

    /**
     * @return void
     */
    private function setCurrentUser(): void
    {
        if(!empty($GLOBALS['USER']) && $GLOBALS['USER'] instanceof CUser)
        {
            $this->user = CurrentUser::get();
        }
    }

    /**
     * @return int
     */
    protected function getCurrentUserId(): int
    {
        return !empty($this->user) ? $this->user->getId() : 0;
    }
}