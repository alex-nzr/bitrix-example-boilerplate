<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - FilterFactory.php
 * 24.11.2022 14:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Filter;

use Bitrix\Crm\Filter\Factory;
use Bitrix\Crm\Filter\Filter as CrmFilter;
use Bitrix\Crm\Filter\ItemSettings;
use Bitrix\Main\Filter\DataProvider;
use Bitrix\Main\Filter\EntitySettings;
use Vendor\Project\Dynamic\Service\Container;
use Vendor\Project\Dynamic\Entity;

/**
 * Class FilterFactory
 * @package Vendor\Project\Dynamic\Filter
 */
class FilterFactory extends Factory
{
    /**
     * @param \Bitrix\Main\Filter\EntitySettings $settings
     * @return \Bitrix\Main\Filter\DataProvider
     * @throws \Exception
     */
    public function getDataProvider(EntitySettings $settings): DataProvider
    {
        if ($settings instanceof ItemSettings)
        {
            $entityTypeId = $settings->getType()->getEntityTypeId();
            $tenderEntityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
            if ($entityTypeId === $tenderEntityTypeId)
            {
                $factory = Container::getInstance()->getFactory($entityTypeId);
                if ($factory)
                {
                    return new ItemDataProvider($settings, $factory);
                }
            }
        }

        return parent::getDataProvider($settings);
    }

    /**
     * @param \Bitrix\Main\Filter\EntitySettings $settings
     * @return \Bitrix\Main\Filter\DataProvider
     * @throws \Exception
     */
    public function getUserFieldDataProvider(EntitySettings $settings): DataProvider
    {
        if ($settings instanceof ItemSettings)
        {
            $entityTypeId = $settings->getType()->getEntityTypeId();
            $tenderEntityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
            if ($entityTypeId === $tenderEntityTypeId)
            {
                return new ItemUfDataProvider($settings);
            }
        }

        return parent::getUserFieldDataProvider($settings);
    }

    public function createFilter(
        $ID, DataProvider $entityDataProvider, array $extraDataProviders = null, array $params = null
    ): CrmFilter
    {
        if ($entityDataProvider instanceof ItemDataProvider)
        {
            return new Filter($ID, $entityDataProvider, (array)$extraDataProviders, (array)$params);
        }

        return parent::createFilter($ID, $entityDataProvider, (array)$extraDataProviders, (array)$params);
    }
}