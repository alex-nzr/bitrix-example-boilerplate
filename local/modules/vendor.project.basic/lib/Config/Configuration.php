<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Configuration.php
 * 24.11.2022 12:00
 * ==================================================
 */
namespace Vendor\Project\Basic\Config;

use Vendor\Project\Basic\Internals\Control\ServiceManager;

/**
 * Class Configuration
 * @package Vendor\Project\Basic\Config
 */
final class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Vendor\Project\Basic\Config\Configuration
     */
    public static function getInstance(): Configuration
    {
        if (Configuration::$instance === null)
        {
            Configuration::$instance = new Configuration();
        }
        return Configuration::$instance;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return '/'.ServiceManager::getModuleParentDirectoryName().'/modules/'.ServiceManager::getModuleId().'/log.txt';
    }

    private function __clone(){}
    public function __wakeup(){}
}