<?php
if (is_file($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/vendor/autoload.php'))
{
    require_once ( $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/vendor/autoload.php' );
    \MyCompany\Example\EventCollector::bindEvents();
}