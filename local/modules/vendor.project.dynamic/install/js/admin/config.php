<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * "Bit.Umc - Bitrix integration" - config.php
 * 10.07.2022 22:37
 * ==================================================
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/admin.bundle.css',
	'js'  => 'dist/admin.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'color_picker',
	],
	'skip_core' => true,
];