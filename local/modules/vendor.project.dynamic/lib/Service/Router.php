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
use Bitrix\Main\Context;
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
    private ?bool $isDetailPage = null;
    private ?bool $isListPage = null;
    private int $entityTypeId;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->entityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
        parent::__construct();
    }

    /**
     * @param \Bitrix\Main\HttpRequest|null $httpRequest
     * @return \Bitrix\Crm\Service\Router\ParseResult
     */
    public function parseRequest(HttpRequest $httpRequest = null): ParseResult
    {
        $result       = parent::parseRequest($httpRequest);
        $component    = $result->getComponentName();
        $parameters   = $result->getComponentParameters();
        $entityTypeId = $parameters['ENTITY_TYPE_ID'] ?? $parameters['entityTypeId'] ?? null;

        if ((int)$entityTypeId === $this->entityTypeId)
        {
            $newComponent = $component;
            switch ($component)
            {
                case 'bitrix:crm.item.list':
                    //TODO add to the list component after replacement
                    ServiceManager::getInstance()->addListPageExtensions();
                    //$newComponent = 'myNewListComponent';

                    $this->isListPage = true;
                    $this->isDetailPage = false;
                    break;

                case 'bitrix:crm.item.details':
                    //detail page extensions called in EventManager
                    //$newComponent = 'myNewDetailComponent';

                    $this->isListPage = false;
                    $this->isDetailPage = true;
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
     * @return string
     */
    public function getItemListUrlInCrmSection(): string
    {
        return '/crm/type/'.$this->entityTypeId.'/list';
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isInDynamicTypeSection(): bool
    {
        $page    = $this->getCurPage();
        $needle1 = '/crm/type/' . $this->entityTypeId . '/';
        $needle2 = "/page/" . Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE . "/";

        return ( (strpos($page, $needle1) === 0) || (strpos($page, $needle2) === 0) );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isDetailPage(): bool
    {
        if ($this->isDetailPage === null)
        {
            $page               = $this->getCurPage();
            $needle             = '/type/' . $this->entityTypeId . '/details/';
            $this->isDetailPage = (strpos($page, $needle) !== false);
        }
        return $this->isDetailPage;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isListPage(): bool
    {
        if ($this->isListPage === null)
        {
            $page    = $this->getCurPage();
            $needle1 = $this->getItemListUrlInCustomSection();
            $needle2 = $this->getItemListUrlInCrmSection();
            $this->isListPage = ( (strpos($page, $needle1) === 0) || (strpos($page, $needle2) === 0) );
        }
        return $this->isListPage;
    }

    /**
     * @return string
     */
    public function getCurPage(): string
    {
        return (string)Context::getCurrent()->getRequest()->getRequestedPage();
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getItemDetailUrlById(int $id): string
    {
        return $this->getItemDetailUrl( $this->entityTypeId, $id);
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
        $parts = explode( '/type/'.$this->entityTypeId.'/details/',$requestedPage);
        if (!empty($parts[1]))
        {
            $id = current(explode('/', $parts[1]));
            return is_numeric($id) ? (int)$id : null;
        }
        return null;
    }
}