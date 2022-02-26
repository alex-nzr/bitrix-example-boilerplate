<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');
/**
 * @var CMain $APPLICATION
 */
CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty('Title', "Страница не найдена");
?>
    <h1><?php $APPLICATION->ShowTitle(false);?></h1>
    <?php $APPLICATION->IncludeComponent(
        "bitrix:main.map",
        ".default",
        Array(
            "LEVEL" 	        =>	"3",
            "COL_NUM"	        =>	"2",
            "SHOW_DESCRIPTION"	=>	"Y",
            "SET_TITLE"	        =>	"Y",
            "CACHE_TIME"	    =>	"36000000"
        )
    );?>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>