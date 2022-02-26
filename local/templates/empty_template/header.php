<?php
/**
 * @var CMain $APPLICATION
 */
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php $APPLICATION->ShowHead();?>
		<title><?php $APPLICATION->ShowTitle();?></title>
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" /> 	
	</head>
	<body>
		<div id="panel">
			<?php $APPLICATION->ShowPanel();?>
		</div>