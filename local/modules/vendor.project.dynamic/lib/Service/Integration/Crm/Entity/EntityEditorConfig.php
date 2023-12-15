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

use Bitrix\Crm\Attribute\FieldAttributeManager;
use Bitrix\Crm\Entity\EntityEditorConfigScope;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use CCrmOwnerType;
use Exception;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Internals\Contract\IEditorConfig;
use Vendor\Project\Dynamic\Internals\EditorConfig;
use Vendor\Project\Dynamic\Service\EntityEditor\FieldManager;

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
            return CCrmOwnerType::ResolveName($this->entityTypeID) .'_details_C'. $categoryId;
        }
        return parent::getConfigId();
    }

    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     */
    public static function setTypeCardConfig(int $entityTypeId): Result
    {
        $result = new Result();
        try
        {
            $typeId = Configuration::getInstance()->getTypeId();
            if ($typeId <= 0)
            {
                throw new Exception('Error in '.__METHOD__.': typeId must be greater than 0');
            }

            $userId     = !empty($GLOBALS['USER']) ? CurrentUser::get()->getId() : 1;
            $scope      = EntityEditorConfigScope::COMMON;
            $categories = ItemCategoryTable::query()
                ->setSelect(['ID', 'CODE', 'IS_DEFAULT'])
                ->where('ENTITY_TYPE_ID', $entityTypeId)
                ->fetchCollection();

            //clean attributes before save new configuration
            FieldAttributeManager::deleteByOwnerType($entityTypeId);

            foreach ($categories as $category)
            {
                $categoryId = $category->getId();
                $code = (string)$category->get('CODE');
                if (empty($code) && ($category->get('IS_DEFAULT') === true))
                {
                    $code = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
                }

                $editorConfig = EditorConfig\Factory::getInstance($typeId, $entityTypeId)->createConfig($code);

                if ($editorConfig instanceof IEditorConfig)
                {
                    $cardConfiguration = $editorConfig->getEditorConfigTemplate();
                    if (!empty($cardConfiguration))
                    {
                        $extras = [
                            'CATEGORY_ID' => $categoryId,
                        ];

                        $crmConfig = new static($entityTypeId, $userId, $scope, $extras);
                        $data      = $crmConfig->normalize($cardConfiguration);
                        $data      = $crmConfig->sanitize($data);
                        $crmConfig->set($data);

                        foreach ($editorConfig->getRequiredFields() as $requiredFieldName => $requiredFieldConfig)
                        {
                            FieldManager::getInstance($entityTypeId)->saveFieldAsRequired(
                                $requiredFieldName, $categoryId, $requiredFieldConfig
                            );
                        }
                    }
                    else
                    {
                        $result->addError(new Error("Can not find card configuration for category '$code'"));
                    }
                }
                else
                {
                    $result->addError(new Error("Can not create editorConfig object"));
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