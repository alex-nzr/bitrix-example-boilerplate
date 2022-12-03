<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Dynamic.php
 * 24.11.2022 12:17
 * ==================================================
 */

namespace Vendor\Project\Dynamic\Entity;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\Config\Option;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Main\Result;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use Vendor\Project\Dynamic\Service\Container;
use CCrmStatus;

/**
 * Class Dynamic
 * @package Vendor\Project\Dynamic\Entity
 */
class Dynamic
{
    /** @var \Bitrix\Crm\Model\Dynamic\TypeTable|string */
    private       $typeDataClass;
    private ?Type $typeObject;
    private int   $entityTypeId;
    private int   $typeId;
    private array $customCategories = [];

    private static ?Dynamic $instance = null;

    /**
     * Dynamic constructor.
     * @param int $typeId
     * @throws \Exception
     */
    private function __construct(int $typeId)
    {
        $this->typeId        = $typeId;
        $this->typeDataClass = Container::getInstance()->getDynamicTypeDataClass();
        $this->setTypeObject();
        $this->setEntityTypeId();
    }

    /**
     * @return \Vendor\Project\Dynamic\Entity\Dynamic
     * @throws \Exception
     */
    public static function getInstance(): Dynamic
    {
        if(static::$instance === null)
        {
            $typeId = (int)Option::get(
                ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID
            );
            static::$instance = new static($typeId);
        }
        return static::$instance;
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
     */
    public function getEntityTypeId(): int
    {
        return $this->entityTypeId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getDefaultCategoryId(): int
    {
        $category = $this->getItemFactory()->getDefaultCategory();
        return !empty($category) ? (int)$category->getId() : 0;
    }

    /**
     * @param string $title
     * @return int
     * @throws \Exception
     */
    public function getCustomCategoryIdByTitle(string $title): int
    {
        if (!in_array($title, $this->customCategories))
        {
            foreach($this->getItemFactory()->getCategories() as $category)
            {
                if($category->getName() === $title)
                {
                    $this->customCategories[$category->getId()] = $title;
                }
            }
        }
        return (int)array_search($title, $this->customCategories);
    }

    /**
     * @return \Bitrix\Crm\Service\Factory|null
     * @throws \Exception
     */
    public function getItemFactory(): ?Factory
    {
        return Container::getInstance()->getFactory($this->entityTypeId);
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getStatusPrefix(int $categoryId): string
    {
        return CCrmStatus::getDynamicEntityStatusPrefix($this->entityTypeId, $categoryId) . ":";
    }

    /**
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function add(array $fields): Result
    {
        $item = $this->getItemFactory()->createItem();

        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }

        $saveOperation = $this->getItemFactory()->getAddOperation($item);
        $res = $saveOperation->disableCheckAccess()->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function update(Item $item, array $fields): Result
    {
        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }
        $updateOperation = $this->getItemFactory()->getUpdateOperation($item);
        $res = $updateOperation->disableAllChecks()->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function delete(Item $item): Result
    {
        $deleteOperation = $this->getItemFactory()->getDeleteOperation($item);
        return $deleteOperation->launch();
    }

    /**
     * @param array $select
     * @param array $filter
     * @param bool $checkPermissions
     * @return \Bitrix\Crm\Item[]
     * @throws \Exception
     */
    public function select(array $select = [], array $filter = [], bool $checkPermissions = false): array
    {
        $params = [
            'order'  => ['ID' => 'ASC'],
            'select' => count($select) > 0 ? $select : ['ID'],
            'filter' => count($filter) > 0 ? $filter : [],
        ];

        if ($checkPermissions)
        {
            return $this->getItemFactory()->getItemsFilteredByPermissions($params);
        }
        else
        {
            return $this->getItemFactory()->getItems($params);
        }
    }

    /**
     * @param int $entityID
     * @return \Bitrix\Crm\Item|null
     * @throws \Exception
     */
    public function getById(int $entityID): ?Item
    {
        $items = $this->select(['*', 'UF_*'], ['=ID' => $entityID]);
        if (!empty($items))
        {
            return current($items);
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    private function setTypeObject(): void
    {
        $this->typeObject = $this->typeDataClass::getByPrimary($this->typeId)->fetchObject();
    }

    /**
     * @return void
     */
    private function setEntityTypeId(): void
    {
        $this->entityTypeId = !empty($this->typeObject) ? (int)$this->typeObject->getEntityTypeId() : 0;
    }

    private function __clone() {}
    public  function __wakeup() {}
}