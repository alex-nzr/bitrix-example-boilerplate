<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/vuetest.bundle.css',
	'js' => 'dist/vuetest.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue3',
	],
	'skip_core' => false,
];