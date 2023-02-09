<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - CustomSectionProvider.php
 * 24.11.2022 14:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service\Integration\Intranet;

use Bitrix\Crm\Integration\Intranet\CustomSectionProvider as IntranetCustomSectionProvider;
use Bitrix\Crm\Integration\IntranetManager;
use Bitrix\Intranet\CustomSection\Provider\Component;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Web\Uri;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Service\Container;
use CCrmOwnerType;

/**
 * Class CustomSectionProvider
 * @package Vendor\Project\Dynamic\Service\Integration\Intranet
 */
class CustomSectionProvider extends IntranetCustomSectionProvider
{
    const DEFAULT_LIST_COMPONENT = 'bitrix:crm.item.list';

    /**
     * @param string $pageSettings
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
	public function isAvailable(string $pageSettings, int $userId): bool
	{
        $entityTypeId = $this->getEntityTypeIdByPageSettings($pageSettings);

        if (empty($entityTypeId) || !CCrmOwnerType::IsDefined($entityTypeId))
        {
            return false;
        }

        return $this->checkPermissionsByPageSettings($pageSettings, $userId, $entityTypeId);
	}

    /**
     * @param string $pageSettings
     * @param \Bitrix\Main\Web\Uri $url
     * @return \Bitrix\Intranet\CustomSection\Provider\Component|null
     * @throws \Exception
     */
    public function resolveComponent(string $pageSettings, Uri $url): ?Component
    {
        $entityTypeId = $this->getEntityTypeIdByPageSettings($pageSettings);

        if (is_null($entityTypeId) || !CCrmOwnerType::isPossibleDynamicTypeId($entityTypeId))
        {
            return null;
        }

        $customSections = IntranetManager::getCustomSections();
        if (is_null($customSections))
        {
            return null;
        }

        $router = Container::getInstance()->getRouter();
        $componentParameters = [];
        foreach ($customSections as $section)
        {
            foreach ($section->getPages() as $page)
            {
                $entityTypeId = $this->getEntityTypeIdByPageSettings($page->getSettings());

                if (($entityTypeId > 0) && ($page->getSettings() === $pageSettings))
                {
                    $url = IntranetManager::getUrlForCustomSectionPage($section->getCode(), $page->getCode());
                    $componentParameters = [
                        'root' => ($url ? $url->getPath() : null),
                    ];

                    $router->setDefaultComponent($this->getComponentByPageSettings($pageSettings));
                    $router->setDefaultComponentParameters([
                        'entityTypeId' => $entityTypeId,
                    ]);
                }
            }
        }

        return (new Component())
            ->setComponentTemplate('')
            ->setComponentName('bitrix:crm.router')
            ->setComponentParams($componentParameters);
    }

    /**
     * @param string $pageSettings
     * @return int|mixed|string|null
     */
    public function getEntityTypeIdByPageSettings(string $pageSettings)
    {
        $customPagesMap = Configuration::getInstance()->getCustomPagesMap();
        $set = explode("_", $pageSettings);

        if (array_key_exists($set[1], $customPagesMap))
        {
            $entityTypeId = $set[0];
        }
        else
        {
            $entityTypeId = IntranetManager::getEntityTypeIdByPageSettings($pageSettings);
        }

        return $entityTypeId;
    }

    /**
     * @param string $pageSettings
     * @return string
     */
    public function getComponentByPageSettings(string $pageSettings): string
    {
        $customPagesMap = Configuration::getInstance()->getCustomPagesMap();
        $set = explode("_", $pageSettings);

        if (array_key_exists($set[1], $customPagesMap))
        {
            return $customPagesMap[$set[1]]['COMPONENT'];
        }
        else
        {
            return static::DEFAULT_LIST_COMPONENT;
        }
    }

    /**
     * @param string $pageSettings
     * @param int $userId
     * @param int $entityTypeId
     * @return bool
     * @throws \Exception
     */
    public function checkPermissionsByPageSettings(string $pageSettings, int $userId, int $entityTypeId): bool
    {
        $set = explode("_", $pageSettings);
        if (is_array($set) && $set[1] === Constants::CUSTOM_PAGE_EXAMPLE)
        {
            return CurrentUser::get()->isAdmin();
        }

        return Container::getInstance()->getUserPermissions($userId)->checkReadPermissions($entityTypeId);
    }
}
