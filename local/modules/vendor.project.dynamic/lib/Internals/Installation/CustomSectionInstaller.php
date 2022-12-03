<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - CustomSectionInstaller.php
 * 24.11.2022 20:23
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\Installation;

use Bitrix\Crm\Integration\IntranetManager;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Service\Container;
use Exception;

/**
 * Class CustomSectionInstaller
 * @package Vendor\Project\Dynamic\Internals\Installation
 */
class CustomSectionInstaller
{
    private static array $pagesMap = [];

    /**
     * @return \Bitrix\Main\Orm\Data\UpdateResult | \Bitrix\Main\Orm\Data\AddResult
     * @throws \Exception
     */
    public static function installCustomSection()
    {
        if (!IntranetManager::isCustomSectionsAvailable())
        {
            throw new Exception('Intranet custom sections is unavailable');
        }

        $title           = Constants::DYNAMIC_TYPE_CUSTOM_SECTION_TITLE;
        $code            = Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE;
        $existsSectionId = static::getCustomSectionId($code);

        if ((int)$existsSectionId > 0)
        {
            return CustomSectionTable::update($existsSectionId, [
                'TITLE' => $title
            ]);
        }
        else
        {
            return CustomSectionTable::add([
                'TITLE'     => $title,
                'CODE'      => $code,
                'MODULE_ID' => 'crm',
            ]);
        }
    }

    /**
     * @param string|null $code
     * @return int|null
     * @throws \Exception
     */
    protected static function getCustomSectionId(?string $code): ?int
    {
        if (!empty($code))
        {
            $existsSection = CustomSectionTable::query()
                ->setFilter([
                    'CODE' => $code,
                    'MODULE_ID' => 'crm'
                ])
                ->setSelect(['ID'])
                ->fetch();

            if (!empty($existsSection))
            {
                return (int)$existsSection['ID'];
            }
        }
        return null;
    }

    /**
     * @param int $entityTypeId
     * @param int $customSectionId
     * @throws \Exception
     */
    public static function installCustomPages(int $entityTypeId, int $customSectionId): void
    {
        static::$pagesMap = Configuration::getInstance()->getCustomPagesMap();

        $pagesSettings = [];
        foreach (static::$pagesMap as $pageCode => $pageData)
        {
            $pagesSettings[$pageCode] = $entityTypeId . '_' . $pageCode;
        }

        $pages = CustomSectionPageTable::query()
            ->setSelect(['ID', 'SETTINGS'])
            ->setFilter([
                '=SETTINGS'          => $pagesSettings,
                '=CUSTOM_SECTION_ID' => $customSectionId,
            ])
            ->fetchAll();

        $pagesToUpdate = [];
        $pagesToDelete = [];

        if (!empty($pages))
        {
            foreach ($pages as $page)
            {
                $code = array_search($page['SETTINGS'], $pagesSettings);
                if (is_string($code) && !empty($code))
                {
                    $pagesToUpdate[$page['ID']] = $code;
                    unset($pagesSettings[$code]);
                }
                else
                {
                    $pagesToDelete[] = $page['ID'];
                }
            }
        }

        $pagesToAdd = $pagesSettings;

        static::addCustomPages($pagesToAdd, $customSectionId);
        static::updateCustomPages($pagesToUpdate);
        static::deleteCustomPages($pagesToDelete);

        Container::getInstance()->getRouter()->reInit();
    }

    /**
     * @param array $pages
     * @param int $customSectionId
     * @throws \Exception
     */
    protected static function addCustomPages(array $pages, int $customSectionId): void
    {
        $sort = 0;
        foreach ($pages as $pageCode => $pageSettings)
        {
            $sort += 100;
            CustomSectionPageTable::add([
                'TITLE' => static::$pagesMap[$pageCode]['TITLE'],
                'MODULE_ID' => 'crm',
                'CUSTOM_SECTION_ID' => $customSectionId,
                'SETTINGS' => $pageSettings,
                'SORT' => $sort,
                'CODE' => $pageCode
            ]);
        }
    }

    /**
     * @param array $pages
     * @throws \Exception
     */
    protected static function updateCustomPages(array $pages): void
    {
        foreach ($pages as $pageId => $pageCode)
        {
            CustomSectionPageTable::update($pageId, [
                'TITLE' => static::$pagesMap[$pageCode]['TITLE'],
            ]);
        }
    }

    /**
     * @param array $pageIds
     * @throws \Exception
     */
    protected static function deleteCustomPages(array $pageIds): void
    {
        foreach ($pageIds as $pageId)
        {
            CustomSectionPageTable::delete($pageId);
        }
    }

    /**
     * @throws \Exception
     */
    public static function uninstallCustomSection(): void
    {
        $existsSection = CustomSectionTable::query()
            ->setFilter([
                'CODE'      => Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE,
                'MODULE_ID' => 'crm'
            ])
            ->setSelect(['ID', 'TITLE'])
            ->fetch();

        if (!empty($existsSection))
        {
            $existsPages = CustomSectionPageTable::query()
                ->setSelect(['ID'])
                ->setFilter([
                    'CUSTOM_SECTION_ID' => $existsSection['ID']
                ])
                ->fetchAll();

            if (!empty($existsPages))
            {
                foreach ($existsPages as $existsPage)
                {
                    CustomSectionPageTable::delete($existsPage['ID']);
                }
            }

            CustomSectionTable::delete($existsSection['ID']);
        }
    }
}