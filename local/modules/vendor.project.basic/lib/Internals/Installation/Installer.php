<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Installer.php
 * 24.11.2022 19:17
 * ==================================================
 */
namespace Vendor\Project\Basic\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Vendor\Project\Basic\Config\Constants;
use Vendor\Project\Basic\Internals\Control\ServiceManager;

Loc::loadMessages(__FILE__);
/**
 * Class Installer
 * @package Vendor\Project\Basic\Internals\Installation
 */
class Installer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function installModule(): Result
    {
        $finalRes = new Result();
        DBTableInstaller::install();
        return $finalRes;
    }

    /**
     * @throws \Exception
     */
    public static function uninstallModule()
    {
        DBTableInstaller::uninstall();
    }
}