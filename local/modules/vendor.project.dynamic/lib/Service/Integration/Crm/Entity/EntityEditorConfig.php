<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * gpnsm - EntityEditorConfig.php
 * 24.11.2022 13:46
 * ==================================================
 */

namespace Vendor\Project\Dynamic\Service\Integration\Crm\Entity;

use Bitrix\Crm\Entity\EntityEditorConfigScope;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Exception;

/**
 * Class EntityEditorConfig
 * @package Vendor\Project\Dynamic\Service\Integration\Crm\Entity
 */
class EntityEditorConfig extends \Bitrix\Crm\Entity\EntityEditorConfig
{
    /**
     * Factory in \Bitrix\Crm\Entity\EntityEditorConfig can not find additional categories while module installing,
     * that's why needle to manually set an ID of card's configuration for category
     * @return string
     * @throws \Exception
     */
    protected function getConfigId(): string
    {
        $categoryId = $this->extras['CATEGORY_ID'];
        if (is_numeric($categoryId) && (int)$categoryId > 0)
        {
            return 'DYNAMIC_'. $this->entityTypeID .'_details_C'. $categoryId;
        }
        return parent::getConfigId();
    }

    private static function getDefaultEditorConfig($typeId): array
    {
        return [
            [
                'name' =>  "example",
                'title' => "Example section",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $typeId ."_EXAMPLE_STRING",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_EXAMPLE_LIST",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $typeId ."_EXAMPLE_DATE",
                        'optionFlags' => '1'
                    ],
                ]
            ],
        ];
    }

    /**
     * @param int $entityTypeId
     * @param int $typeId
     * @return \Bitrix\Main\Result
     */
    public static function setTypeCardConfig(int $entityTypeId, int $typeId): Result
    {
        $result = new Result();
        try
        {
            $userID            = !empty($GLOBALS['USER']) ? CurrentUser::get()->getId() : 1;
            $scope             = EntityEditorConfigScope::COMMON;
            $cardConfiguration = static::getDefaultEditorConfig($typeId);

            if (!empty($cardConfiguration))
            {
                $categories = ItemCategoryTable::query()->where('ENTITY_TYPE_ID', $entityTypeId)->fetchCollection();
                foreach ($categories as $category)
                {
                    $extras = [
                        'CATEGORY_ID' => $category->getId(),
                    ];

                    $config = new static($entityTypeId, $userID, $scope, $extras);
                    $data = $config->normalize($cardConfiguration);
                    $data = $config->sanitize($data);
                    $config->set($data);
                }
            }
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }
}