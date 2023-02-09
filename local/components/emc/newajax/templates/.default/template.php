<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form id="testForm">
    <input type="file" name="testFile">
    <input type="text" name="testText">
    <?=bitrix_sessid_post()?>
    <input type="submit" name="submit" value="submit">
</form>
<p id="testActionRes"></p>
