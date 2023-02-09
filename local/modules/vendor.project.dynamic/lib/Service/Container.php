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
use Vendor\Project\Dynamic\Service\Access\UserPermissions;

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
        if ($entityTypeId === Entity\Dynamic::getInstance()->getEntityTypeId())
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
}