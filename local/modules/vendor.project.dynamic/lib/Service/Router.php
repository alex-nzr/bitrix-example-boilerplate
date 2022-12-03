<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Router.php
 * 24.11.2022 14:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service;

use Bitrix\Crm\Service\Router\ParseResult;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Bitrix\Main\HttpRequest;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Entity;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;

/**
 * Class Router
 * @package Vendor\Project\Dynamic\Service
 */
class Router extends \Bitrix\Crm\Service\Router
{
    /**
     * @param \Bitrix\Main\HttpRequest|null $httpRequest
     * @return \Bitrix\Crm\Service\Router\ParseResult
     * @throws \Exception
     */
    public function parseRequest(HttpRequest $httpRequest = null): ParseResult
    {
        $result       = parent::parseRequest($httpRequest);
        $component    = $result->getComponentName();
        $parameters   = $result->getComponentParameters();
        $entityTypeId = $parameters['ENTITY_TYPE_ID'] ?? $parameters['entityTypeId'] ?? null;

        if ((int)$entityTypeId === Entity\Dynamic::getInstance()->getEntityTypeId())
        {
            $newComponent = $component;
            switch ($component)
            {
                case 'bitrix:crm.item.list':
                    //TODO add to the list component after replacement
                    ServiceManager::getInstance()->addListPageExtensions();
                    //$newComponent = 'myNewListComponent';
                    break;

                case 'bitrix:crm.item.details':
                    //TODO add to the detail component after replacement
                    ServiceManager::getInstance()->addDetailPageExtensions();
                    //$newComponent = 'myNewDetailComponent';
                    break;
            }

            $result = new ParseResult( $newComponent, $parameters, $result->getTemplateName() );
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCustomSectionRoot(): string
    {
        return Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE . "/";
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getItemListUrlInCustomSection(): string
    {
        return '/page/' . $this->getCustomSectionRoot() . $this->getCustomPageCode(Constants::CUSTOM_PAGE_LIST);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getItemDetailUrlById(int $id): string
    {
        return $this->getItemDetailUrl( Entity\Dynamic::getInstance()->getEntityTypeId(), $id);
    }

    /**
     * @param string $settingsKey
     * @return string
     * @throws \Exception
     */
    public function getCustomPageCode(string $settingsKey): string
    {
        $existsPages = Container::getInstance()
                        ->getRouter()
                        ->getCustomSectionPages(Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE);

        $pageCode = '';

        foreach ($existsPages as $existsPage)
        {
            if (strpos($existsPage['SETTINGS'], $settingsKey) !== false)
            {
                $pageCode = $existsPage['CODE'];
            }
        }

        return $pageCode;
    }

    /**
     * @param $sectionCode
     * @return array
     * @throws \Exception
     */
    public function getCustomSectionPages($sectionCode): array
    {
        $existsSection = CustomSectionTable::query()
            ->setFilter([
                'CODE'      => $sectionCode,
                'MODULE_ID' => ServiceManager::getModuleId()
            ])
            ->setSelect(['ID', 'TITLE'])
            ->fetch();

        if (!empty($existsSection))
        {
            return CustomSectionPageTable::query()
                ->setSelect(['ID', 'CODE', 'SETTINGS'])
                ->setFilter(['CUSTOM_SECTION_ID' => $existsSection['ID']])
                ->fetchAll();
        }
        return [];
    }

    /**
     * @param string $requestedPage
     * @return int|null
     * @throws \Exception
     */
    public function getEntityIdFromDetailUrl(string $requestedPage): ?int
    {
        $parts = explode( '/type/'.Entity\Dynamic::getInstance()->getEntityTypeId().'/details/',$requestedPage);
        if (!empty($parts[1]))
        {
            $id = current(explode('/', $parts[1]));
            return is_numeric($id) ? (int)$id : null;
        }
        return null;
    }
}