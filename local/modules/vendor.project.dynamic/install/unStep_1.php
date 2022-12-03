<?php
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;


if (!check_bitrix_sessid()) {
    return;
}

$request = Context::getCurrent()->getRequest();
$moduleId = GetModuleID(__FILE__);
?>
<p><?=Loc::getMessage($moduleId."_IF_INSTALL_ERROR")?></p>
<form action="<?=$request->getRequestedPage();?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="id" value="<?= GetModuleID(__FILE__)?>">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?php CAdminMessage::ShowMessage(Loc::getMessage($moduleId."_UNINSTALL_WARN"))?>
    <p><?=Loc::getMessage($moduleId."_UNINSTALL_SAVE")?></p>
    <label for="saveData" style="display: block; margin-bottom: 20px">
        <input type="checkbox" name="saveData" id="saveData" value="Y" checked>
        <?=Loc::getMessage($moduleId."_UNINSTALL_SAVE_TABLES")?>
    </label>
    <?=bitrix_sessid_post()?>
	<input type="submit" name="ok" value="<?=Loc::getMessage($moduleId."_UNINSTALL_ACCEPT"); ?>">
<form>