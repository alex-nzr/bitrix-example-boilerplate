<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - UserPermissions.php
 * 04.03.2023 01:12
 * ==================================================
 */


namespace Vendor\Project\Basic\Service\Access;

use Bitrix\Main\Engine\CurrentUser;
use CUser;

/**
 * @class UserPermissions
 * @package Vendor\Project\Basic\Service\Access
 */
class UserPermissions
{
    /**
     * @var \Bitrix\Main\Engine\CurrentUser
     */
    private CurrentUser $user;

    public function __construct()
    {
        $this->user = $this->getCurrentUser();
    }

    /**
     * @return bool
     */
    public function canManageModuleOptions(): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return \Bitrix\Main\Engine\CurrentUser
     */
    private function getCurrentUser(): CurrentUser
    {
        if (!($GLOBALS['USER'] instanceof CUser))
        {
            $GLOBALS['USER'] = new CUser();
        }
        return CurrentUser::get();
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->user->isAdmin();
    }
}