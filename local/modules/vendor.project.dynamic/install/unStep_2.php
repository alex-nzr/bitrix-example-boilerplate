<?php
global $APPLICATION;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}
$moduleId = GetModuleID(__FILE__);
$request = Context::getCurrent()->getRequest();

if ($ex = $APPLICATION->GetException())
{
    CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage($moduleId."_UNINSTALL_ERROR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true,
    ));
}
else
{
    CAdminMessage::ShowNote(Loc::getMessage($moduleId."_UNINSTALL_OK"));
}
?>
<form action="<?=$request->getRequestedPage();?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="submit" name="" value="<?=Loc::getMessage($moduleId."_BTN_BACK_TEXT"); ?>">
<form>