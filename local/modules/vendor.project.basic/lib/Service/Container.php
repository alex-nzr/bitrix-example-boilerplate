<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - Container.php
 * 04.03.2023 01:13
 * ==================================================
 */

namespace Vendor\Project\Basic\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Exception;
use Vendor\Project\Basic\Service\Access\UserPermissions;

/**
 * @class Container
 * @package Vendor\Project\Basic\Service
 */
class Container
{
    /**
     * @return \Vendor\Project\Basic\Service\Container
     * @throws \Exception
     */
    public static function getInstance(): Container
    {
        $identifier = static::getIdentifierByClassName(static::class);
        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @param int|null $userId
     * @return \Vendor\Project\Basic\Service\Access\UserPermissions
     * @throws \Exception
     */
    public function getUserPermissions(?int $userId = null): UserPermissions
    {
        $identifier = static::getIdentifierByClassName(UserPermissions::class, [$userId]);

        if(!ServiceLocator::getInstance()->has($identifier))
        {
            ServiceLocator::getInstance()->addInstance($identifier, new UserPermissions($userId));
        }

        return ServiceLocator::getInstance()->get($identifier);
    }

    /**
     * @param string $className
     * @param array|null $parameters
     * @return string
     * @throws \Exception
     */
    public static function getIdentifierByClassName(string $className, array $parameters = null): string
    {
        $words = explode('\\', $className);
        $identifier = '';
        foreach ($words as $word)
        {
            $identifier .= !empty($identifier) ? '.'.lcfirst($word) : lcfirst($word);
        }

        if (empty($identifier))
        {
            throw new Exception('className should be a valid string');
        }

        if(!empty($parameters))
        {
            $parameters = array_filter($parameters, static function($parameter) {
                return (!empty($parameter) && (is_string($parameter) || is_numeric($parameter)));
            });

            if(!empty($parameters))
            {
                $identifier .= '.' . implode('.', $parameters);
            }
        }

        return $identifier;
    }
}