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
namespace Vendor\Project\Dynamic\Config;

use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Integration\Intranet\CustomSectionProvider;

/**
 * Class Configuration
 * @package Vendor\Project\Dynamic\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Vendor\Project\Dynamic\Config\Configuration
     */
    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return '/'.ServiceManager::getModuleParentDirectoryName().'/modules/'.ServiceManager::getModuleId().'/log.txt';
    }

    /**
     * @return \string[][]
     */
    public function getCustomPagesMap(): array
    {
        return [
            Constants::CUSTOM_PAGE_EXAMPLE => [
                'TITLE'     => 'Some custom page',
                'COMPONENT' => 'vendor:project.dynamic.example-component',
            ],
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => 'List page',
                'COMPONENT' => CustomSectionProvider::DEFAULT_LIST_COMPONENT,
            ],
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}