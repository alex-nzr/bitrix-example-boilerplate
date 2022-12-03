<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - ServiceManager.php
 * 24.11.2022 12:11
 * ==================================================
 */
namespace Vendor\Project\Basic\Internals\Control;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Vendor\Project\Basic\Controller;
use Exception;

/**
 * Class ServiceManager
 * @package Vendor\Project\Basic\Internals\Control
 */
class ServiceManager
{
    private static ?ServiceManager $instance = null;
    private static ?string $moduleId = null;
    private static ?string $moduleParentDirectoryName = null;

    private function __construct(){}

    public static function getInstance(): ServiceManager
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @throws \Exception
     */
    public function includeModule()
    {
        $this->includeControllers();
        $this->includeDependentModules();
        $this->includeDependentExtensions();
    }

    /**
     * @throws \Exception
     */
    private function includeControllers(): void
    {
        $arControllers = [
            Controller\Base::class  => 'lib/Controller/Base.php',
        ];

        Loader::registerAutoLoadClasses(static::getModuleId(), $arControllers);
    }

    /**
     * @throws \Exception
     */
    private function includeDependentModules(): void
    {
        $dependencies = [
            'main',
        ];

        foreach ($dependencies as $dependency) {
            if (!Loader::includeModule($dependency)){
                throw new Exception("Can not include module '$dependency'");
            }
        }
    }

    /**
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    private function includeDependentExtensions(): void
    {
        $dependencies = [
            'ui'
        ];

        foreach ($dependencies as $dependency) {
            Extension::load($dependency);
        }
    }

    /**
     * @return string
     */
    public static function getModuleId(): string
    {
        if (empty(static::$moduleId))
        {
            $arr = explode(DIRECTORY_SEPARATOR, __FILE__);
            $i = array_search("modules", $arr);
            static::$moduleId = (string)$arr[$i + 1];
        }
        return static::$moduleId;
    }

    /**
     * @return string
     */
    public static function getModuleParentDirectoryName(): string
    {
        if (empty(static::$moduleParentDirectoryName))
        {
            $arr = explode(DIRECTORY_SEPARATOR, __FILE__);
            $i = array_search("modules", $arr);
            static::$moduleParentDirectoryName = (string)$arr[$i - 1];
        }
        return static::$moduleParentDirectoryName;
    }

    /**
     * @return string
     */
    public function getCurPage(): string
    {
        return (string)Context::getCurrent()->getRequest()->getRequestedPage();
    }

    private function __clone(){}
    public function __wakeup(){}
}