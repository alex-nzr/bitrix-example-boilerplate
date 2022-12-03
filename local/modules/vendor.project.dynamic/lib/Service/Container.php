<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Container.php
 * 24.11.2022 14:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service;

use Bitrix\Main\DI\ServiceLocator;
use Vendor\Project\Dynamic\Entity;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;

/**
 * Class Container
 * @package Vendor\Project\Dynamic\Service
 */
class Container extends \Bitrix\Crm\Service\Container
{
    /**
     * @return \Vendor\Project\Dynamic\Service\Container
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public static function getInstance(): Container
    {
        $container = ServiceLocator::getInstance()->get('crm.service.container');
        if (!($container instanceof Container)){
            $container = new static();
        }
        return $container;
    }

    /**
     * @return \Vendor\Project\Dynamic\Service\Router
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public function getRouter(): Router
    {
        $router = ServiceLocator::getInstance()->get('crm.service.router');
        if (!($router instanceof Router)){
            $router = new Router();
        }
        return $router;
    }

    /**
     * @throws \Exception
     */
    public function getFactory(int $entityTypeId): ?\Bitrix\Crm\Service\Factory
    {
        if ($entityTypeId === Entity\Dynamic::getInstance()->getEntityTypeId())
        {
            $identifier = ServiceManager::getModuleId() . '.dynamicFactory';//Some unique identifier for service
            if(!ServiceLocator::getInstance()->has($identifier))
            {
                $type = $this->getTypeByEntityTypeId($entityTypeId);
                if($type)
                {
                    $factory = new Factory($type);
                    ServiceLocator::getInstance()->addInstance(
                        $identifier,
                        $factory
                    );
                    return $factory;
                }
                return null;
            }
            return ServiceLocator::getInstance()->get($identifier);
        }

        return parent::getFactory($entityTypeId);
    }
}