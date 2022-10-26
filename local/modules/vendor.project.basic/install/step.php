<?php
use Bitrix\Main\Localization\Loc;
global $APPLICATION;

if (!check_bitrix_sessid()) {
    $APPLICATION->ThrowException("Wrong sessid");
}

$moduleId = GetModuleID(__FILE__);

if ($ex = $APPLICATION->GetException())
{
    CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage($moduleId."_INSTALL_ERROR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true,
    ));
}
else
{
    CAdminMessage::ShowNote(Loc::getMessage($moduleId."_INSTALL_OK"));
}
?>
<form action="<?=$APPLICATION->GetCurPage();?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID ?>">
	<input type="submit" name="submit" value="<?=Loc::getMessage($moduleId."_INSTALL_BACK");?>">
<form>