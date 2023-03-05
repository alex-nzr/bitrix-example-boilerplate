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


namespace Vendor\Project\Dynamic\Internals\Control;

use Bitrix\Main\Config\Configuration as BxConfig;
use Bitrix\Main\Context;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\UI\Extension;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Controller;
use Vendor\Project\Dynamic\Service\Container;
use Vendor\Project\Dynamic\Service\Integration\Intranet\CustomSectionProvider;
use Exception;

/**
 * Class ServiceManager
 * @package Vendor\Project\Dynamic\Internals\Control
 */
class ServiceManager
{
    private static ?ServiceManager $instance = null;
    private static ?string $moduleId = null;
    private static ?string $moduleParentDirectoryName = null;

    private function __construct(){}

    /**
     * @return \Vendor\Project\Dynamic\Internals\Control\ServiceManager
     */
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
    public function includeModule(): void
    {
        $this->includeControllers();
        $this->includeDependentModules();
        $this->includeDependentExtensions();
        $this->includeCustomServices();
        $this->checkAjaxRequest();
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
            'crm',
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
     * @throws \Exception
     */
    private function includeCustomServices(): void
    {
        if (Container::getInstance()->getRouter()->isInDynamicTypeSection())
        {
            $this->addCustomCrmServices();
            $this->addCustomSectionProvider();
        }
    }

    /**
     * @return void
     */
    private function addCustomCrmServices(): void
    {
        ServiceLocator::getInstance()->addInstance('crm.service.container', new Container());
    }

    /**
     * @return void
     */
    private function addCustomSectionProvider(): void
    {
        $crmConfig = BxConfig::getInstance('crm');
        $customSectionConfig = $crmConfig->get('intranet.customSection');
        if (is_array($customSectionConfig))
        {
            $customSectionConfig['provider'] = CustomSectionProvider::class;
        }
        else
        {
            $customSectionConfig = [
                'provider' => CustomSectionProvider::class,
            ];
        }
        $crmConfig->add('intranet.customSection', $customSectionConfig);
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
            static::$moduleId = $arr[$i + 1];
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
            static::$moduleParentDirectoryName = $arr[$i - 1];
        }
        return static::$moduleParentDirectoryName;
    }

    /**
     * @return void
     */
    public function addListPageExtensions(): void
    {
    }

    /**
     * called on 'onEntityDetailsContextReady' in \Vendor\Project\Dynamic\Internals\Control\EventManager
     * @return void
     * @throws \Exception
     */
    public static function addDetailPageExtensions(): void
    {
        Extension::load([
            'vendor.project.dynamic.entity-detail-manager',
            'vendor.project.dynamic.ui-detail'
        ]);
    }

    /**
     * @return void
     */
    private function checkAjaxRequest(): void
    {
        try
        {
            $request = Context::getCurrent()->getRequest();

            //break script while installing or uninstalling of module
            if ($request->get('id') === static::getModuleId()
                && ($request->get('install') === 'Y' || $request->get('uninstall') === 'Y')
            ){
                return;
            }

            $entityTypeId = Configuration::getInstance()->getEntityTypeId();

            if ($request->isAjaxRequest())
            {
                $entityTypeIdCondition = ( (int)$request->get('entityTypeId') === $entityTypeId )
                                      || ( (int)$request->get('ENTITY_TYPE_ID') === $entityTypeId )
                                      || ( (int)$request->get('entityTypeID') === $entityTypeId );
                if ($entityTypeIdCondition)
                {
                    $this->addCustomCrmServices();
                }
                else
                {
                    if ($this->findDynamicSignsInRequest($request))
                    {
                        $this->addCustomCrmServices();
                    }
                }
            }
        }
        catch (Exception $e)
        {
            //log error
        }
    }

    /**
     * @param \Bitrix\Main\Request $request
     * @return bool
     * @throws \Exception
     */
    private function findDynamicSignsInRequest(Request $request): bool
    {
        $params       = $request->getValues();
        $typeId       = Configuration::getInstance()->getTypeId();
        $entityTypeId = Configuration::getInstance()->getEntityTypeId();

        if (is_string($params['FORM']) &&
            (
                (strpos($params['FORM'], 'UF_CRM_' . $typeId) !== false)
                || (strpos($params['FORM'], 'DYNAMIC_' . $entityTypeId) !== false)
            )
        ){
            return true;
        }

        if (is_array($params['FIELDS']))
        {
            $founded = false;
            foreach ($params['FIELDS'] as $field)
            {
                if ( !empty($field['ENTITY_ID']) && ($field['ENTITY_ID'] === 'CRM_'.$typeId) )
                {
                    $founded = true;
                    break;
                }
            }
            if ($founded){
                return true;
            }
        }

        if (is_array($params['data']))
        {
            $founded = false;
            foreach ($params['data'] as $key => $value)
            {
                if ( strpos($key, 'UF_CRM_' . $typeId) !== false )
                {
                    $founded = true;
                    break;
                }
            }
            if ($founded){
                return true;
            }
        }

        return false;
    }

    private function __clone(){}
    public function __wakeup(){}
}