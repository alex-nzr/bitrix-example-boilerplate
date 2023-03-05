<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Configuration.php
 * 24.11.2022 12:00
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Config;

use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Main\Config\Option;
use CCrmStatus;
use Exception;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Container;
use Vendor\Project\Dynamic\Service\Integration\Intranet\CustomSectionProvider;

/**
 * Class Configuration
 * @package Vendor\Project\Dynamic\Config
 */
final class Configuration
{
    private static ?Configuration $instance = null;
    private int $typeId;
    private ?Type $typeObject;

    /**
     * @param int $typeId
     * @throws \Exception
     */
    private function __construct(int $typeId)
    {
        $this->typeId = $typeId;
        $this->setTypeObject();
    }

    /**
     * @return \Vendor\Project\Dynamic\Config\Configuration
     * @throws \Exception
     */
    public static function getInstance(): Configuration
    {
        if (Configuration::$instance === null)
        {
            $typeId = (int)Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID);
            Configuration::$instance = new Configuration($typeId);
        }
        return Configuration::$instance;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return '/'.ServiceManager::getModuleParentDirectoryName().'/modules/'.ServiceManager::getModuleId().'/log.txt';
    }

    /**
     * @return \string[][]
     */
    public function getCustomPagesMap(): array
    {
        return [
            Constants::CUSTOM_PAGE_EXAMPLE => [
                'TITLE'     => 'Some custom page',
                'COMPONENT' => 'vendor:project.dynamic.example-component',
            ],
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => 'List page',
                'COMPONENT' => CustomSectionProvider::DEFAULT_LIST_COMPONENT,
            ],
        ];
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @return string
     */
    public function getTypeCode(): string
    {
        return Constants::DYNAMIC_TYPE_CODE;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getEntityTypeId(): int
    {
        if (is_null($this->typeObject))
        {
            throw new Exception('Type object not initialized in ' . __METHOD__);
        }
        return $this->typeObject->getEntityTypeId();
    }

    /**
     * @throws \Exception
     */
    private function setTypeObject(): void
    {
        $typeDataClass    = Container::getInstance()->getDynamicTypeDataClass();
        $this->typeObject = $typeDataClass::getByPrimary($this->typeId)->fetchObject();
    }

    /**
     * @param int $categoryId
     * @return string
     * @throws \Exception
     */
    public function getStatusPrefix(int $categoryId): string
    {
        return CCrmStatus::getDynamicEntityStatusPrefix($this->getEntityTypeId(), $categoryId) . ":";
    }

    private function __clone(){}
    public function __wakeup(){}
}