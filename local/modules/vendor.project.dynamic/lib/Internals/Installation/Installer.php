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
namespace Vendor\Project\Dynamic\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Integration\Crm\Entity\EntityEditorConfig;

Loc::loadMessages(__FILE__);
/**
 * Class Installer
 * @package Vendor\Project\Dynamic\Internals\Installation
 */
class Installer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function installModule(): Result
    {
        $moduleId = ServiceManager::getModuleId();
        $finalRes = new Result();

        $customSectionResult = CustomSectionInstaller::installCustomSection();
        if (!$customSectionResult->isSuccess())
        {
            $finalRes->addErrors($customSectionResult->getErrors());
        }

        $typeResult = TypeInstaller::install($customSectionResult->getId());
        if (!$typeResult->isSuccess())
        {
            $finalRes->addErrors($typeResult->getErrors());
        }
        else
        {
            Option::set($moduleId, Constants::OPTION_KEY_DYNAMIC_TYPE_ID, (int)$typeResult->getPrimary());
            $entityTypeId = $typeResult->getData()['ENTITY_TYPE_ID'];
            CustomSectionInstaller::installCustomPages($entityTypeId, $customSectionResult->getId());
            DBTableInstaller::install();

            $ufResult = UserFieldInstaller::install();
            if (!$ufResult->isSuccess())
            {
                $finalRes->addErrors($ufResult->getErrors());
            }

            $categoryResult = CategoryInstaller::install($entityTypeId);
            if (!$categoryResult->isSuccess())
            {
                $categoryResult->addErrors($categoryResult->getErrors());
            }

            $cardConfigResult = EntityEditorConfig::setTypeCardConfig($entityTypeId);
            if (!$cardConfigResult->isSuccess())
            {
                $finalRes->addErrors($cardConfigResult->getErrors());
            }
        }

        return $finalRes;
    }

    /**
     * @throws \Exception
     */
    public static function uninstallModule(): void
    {
        $result = TypeInstaller::uninstall();
        if (!$result->isSuccess())
        {
            throw new SystemException(implode("; ", $result->getErrorMessages()));
        }
        else
        {
            CustomSectionInstaller::uninstallCustomSection();
            DBTableInstaller::uninstall();
            Option::delete(ServiceManager::getModuleId());
        }
    }
}