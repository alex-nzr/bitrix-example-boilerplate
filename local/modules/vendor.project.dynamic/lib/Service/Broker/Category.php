<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - Category.php
 * 03.03.2023 21:17
 * ==================================================
 */

namespace Vendor\Project\Dynamic\Service\Broker;

use Bitrix\Crm\Category\Entity\Category as CategoryEntity;
use Bitrix\Crm\Service\Broker;
use Bitrix\Crm\Service\Factory;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Service\Container;

class Category extends Broker
{
    private ?Factory $factory;
    private array $categories = [];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->factory = Container::getInstance()->getFactory(Configuration::getInstance()->getEntityTypeId());
        if ($this->factory)
        {
            foreach ($this->factory->getCategories() as $category)
            {
                $this->categories[$category->getId()] = $this->normalizeCategory($category);
            }
        }
    }

    /**
     * @param int $id
     * @return array|null
     */
    protected function loadEntry(int $id): ?array
    {
        if (array_key_exists($id, $this->categories))
        {
            return $this->categories[$id];
        }

        return null;
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function loadEntries(array $ids): array
    {
        $result = [];
        foreach ($ids as $id)
        {
            if (array_key_exists($id, $this->categories))
            {
                $result[] = $this->categories[$id];
            }
        }
        return $result;
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getCategoryCodeById(int $id): ?string
    {
        if (array_key_exists($id, $this->categories))
        {
            return $this->categories[$id]['CODE'];
        }

        return null;
    }

    /**
     * @param string $code
     * @return int|null
     */
    public function getCategoryIdByCode(string $code): ?int
    {
        $categoryId = null;
        foreach ($this->categories as $id => $category) {
            if ($category['CODE'] === $code)
            {
                $categoryId = $id;
                break;
            }
        }
        return $categoryId;
    }

    /**
     * @param \Bitrix\Crm\Category\Entity\Category $category
     * @return array
     */
    protected function normalizeCategory(CategoryEntity $category): array
    {
        $data = $category->getData();
        if (empty($data['CODE']) && $category->getIsDefault())
        {
            $data['CODE'] = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
        }
        return $data;
    }
}