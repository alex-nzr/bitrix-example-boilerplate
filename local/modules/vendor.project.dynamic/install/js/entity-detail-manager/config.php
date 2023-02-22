<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * gpnsm - config.php
 * 21.02.2023 23:08
 * ==================================================
 */


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}



return [
    'css' => 'dist/index.bundle.css',
    'js' => 'dist/index.bundle.js',
    'rel' => [
		'main.core',
		'ui.stageflow',
	],
    'skip_core' => false,
    'settings'  => [],
    'lang' => ['lang/ru/js_lang.php'],
];