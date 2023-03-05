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
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Filter;
use Vendor\Project\Dynamic\Service\Access\UserPermissions;
use Vendor\Project\Dynamic\Service\Broker;

/**
 * Class Container
 * @package Vendor\Project\Dynamic\Service
 */
class Container extends \Bitrix\Crm\Service\Container
{
    /**
     * @return \Vendor\Project\Dynamic\Service\Container
     * @throws \Exception
     */
    public static function getInstance(): Container
    {
        $identifier = static::getIdentifierByClassName(static::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new static());
        }
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @return \Vendor\Project\Dynamic\Service\Router
     * @throws \Exception
     */
    public function getRouter(): Router
    {
        $identifier = static::getIdentifierByClassName(Router::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Router);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @param int $entityTypeId
     * @return \Bitrix\Crm\Service\Factory|null
     * @throws \Exception
     */
    public function getFactory(int $entityTypeId): ?\Bitrix\Crm\Service\Factory
    {
        if ($entityTypeId === Configuration::getInstance()->getEntityTypeId())
        {
            $identifier = static::getIdentifierByClassName(Factory::class);
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

    /**
     * @param int|null $userId
     * @return \Vendor\Project\Dynamic\Service\Access\UserPermissions
     * @throws \Exception
     */
    public function getUserPermissions(?int $userId = null): UserPermissions
    {
        if($userId === null)
        {
            $userId = $this->getContext()->getUserId();
        }

        $identifier = static::getIdentifierByClassName(UserPermissions::class, [$userId]);

        if(!ServiceLocator::getInstance()->has($identifier))
        {
            $userPermissions = $this->createUserPermissions($userId);
            ServiceLocator::getInstance()->addInstance($identifier, $userPermissions);
        }

        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @param int $userId
     * @return \Bitrix\Crm\Service\UserPermissions
     */
    protected function createUserPermissions(int $userId): \Bitrix\Crm\Service\UserPermissions
    {
        return new UserPermissions($userId);
    }

    /**
     * @return \Vendor\Project\Dynamic\Filter\FilterFactory
     * @throws \Exception
     */
    public function getFilterFactory(): Filter\FilterFactory
    {
        $identifier = static::getIdentifierByClassName(Filter\FilterFactory::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Filter\FilterFactory);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @return \Vendor\Project\Dynamic\Service\Context
     * @throws \Exception
     */
    public function getContext(): Context
    {
        $identifier = static::getIdentifierByClassName(Context::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Context);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @return \Vendor\Project\Dynamic\Service\Broker\UserField
     * @throws \Exception
     */
    public function getUserFieldBroker(): Broker\UserField
    {
        $identifier = static::getIdentifierByClassName(Broker\UserField::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Broker\UserField);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @return \Vendor\Project\Dynamic\Service\Broker\Category
     * @throws \Exception
     */
    public function getCategoryBroker(): Broker\Category
    {
        $identifier = static::getIdentifierByClassName(Broker\Category::class);
        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new Broker\Category);
        }
        return ServiceLocator::getInstance()->get($identifier);
    }
}