<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$watermark = array(
	"PARENT" => "DATA_SOURCE",
	"NAME" => Loc::getMessage("FILTERS"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => "png,gif,jpg,jpeg",
	"FD_UPLOAD" => true,
	"FD_USE_MEDIALIB" => true,
	"FD_MEDIALIB_TYPES" => Array(
		'image',
			),
	"REFRESH" => "N",
	"MULTIPLE" => "N",
	"DEFAULT" => "",
);

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"MAX_FILE_SIZE" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("MAX_FILE_SIZE"),
			"TYPE" => "NUMBER",
			"MULTIPLE" => "N",
			"DEFAULT" => 5,
		),

		"DEFAULT_IMG" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::getMessage("DEFAULT_IMG"),
			"TYPE" => "FILE",
			"FD_TARGET" => "F",
			"FD_EXT" => "png,gif,jpg,jpeg",
			"FD_UPLOAD" => true,
			"FD_USE_MEDIALIB" => true,
			"FD_MEDIALIB_TYPES" => Array(
			    'image',
			),
			"MULTIPLE" => "N",
		),

		"LIMIT" => array(
			"NAME" => Loc::getMessage("LIMIT"),
			"PARENT" => "BASE",
			"TYPE" => "NUMBER",
			"DEFAULT" => 1,	
			"REFRESH" => "Y",
		),

		"SAVE_DEFAULT_FILTERS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => Loc::getMessage("SAVE_DEFAULT_FILTERS"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
		),
		
		"USE_AJAX" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => Loc::getMessage("USE_AJAX"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
		),
	),
);

if ($arCurrentValues['LIMIT'] < 1)
{
	$arCurrentValues['LIMIT'] = 1;
}

for ($i=1; $i <= $arCurrentValues['LIMIT']; $i++)
{
	$arComponentParameters['PARAMETERS']['WATERMARK_'.$i] = array_merge(
		$watermark,
		["NAME" => Loc::getMessage("FILTERS")." ".$i]
	);
}
?>