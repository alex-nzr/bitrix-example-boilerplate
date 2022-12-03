<?php
use Bitrix\Main\Loader;
use Vendor\Project\Routing\Controller\IBlockElementController;

try
{
    $arControllers = [
        IBlockElementController::class  => 'lib/Controller/IBlockElementController.php',
    ];
    Loader::registerAutoLoadClasses(GetModuleID(__FILE__), $arControllers);
}
catch (Exception $e)
{
    //log error
}