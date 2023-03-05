<?php
/** @var \CMain $APPLICATION */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Vendor\Project\Basic\Config\OptionManager;
use Vendor\Project\Basic\Internals\Control\ServiceManager;
use Vendor\Project\Basic\Service\Container;

Loc::loadMessages(__FILE__);

$module_id = ServiceManager::getModuleId();

try
{
    if(!Loader::includeModule($module_id)){
        throw new Exception(Loc::getMessage($module_id." module not included"));
    }

    if (!Container::getInstance()->getUserPermissions()->canManageModuleOptions())
    {
        $APPLICATION->AuthForm(Loc::getMessage("Access to $module_id denied"));
    }

    Extension::load([$module_id.'.admin']);

    $optionManager = new OptionManager($module_id);
    $optionManager->processRequest();
    $optionManager->startDrawHtml();

    /*$optionManager->tabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");*/

    $optionManager->endDrawHtml();
}
catch(Exception $e)
{
    ShowError($e->getMessage());
}