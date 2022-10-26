<?php
use Bitrix\Main\Loader;
use Vendor\Project\Basic\Controller\IBlockElementController;

try
{
    //при ajax-экшенах в битриксе не работает psr-4 для контроллеров, поэтому подключение вручную
    $arControllers = [
        IBlockElementController::class  => 'lib/Controller/IBlockElementController.php',
    ];
    Loader::registerAutoLoadClasses(GetModuleID(__FILE__), $arControllers);
}
catch (Exception $e)
{
    //log error
}