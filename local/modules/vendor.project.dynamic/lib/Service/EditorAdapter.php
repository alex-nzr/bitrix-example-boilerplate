<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - EditorAdapter.php
 * 19.02.2023 01:16
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service;

use Bitrix\Crm\EO_Status_Collection;
use Bitrix\Crm\Field;
use Bitrix\Crm\Item;
use CCrmFieldInfoAttr;
use Vendor\Project\Dynamic\Entity\Dynamic;
use Vendor\Project\Dynamic\Internals\Contract\IEditorConfig;
use Vendor\Project\Dynamic\Service\EntityEditor\FieldManager;

/**
 * @class EditorAdapter
 * @package Vendor\Project\Dynamic\Service
 */
class EditorAdapter extends \Bitrix\Crm\Service\EditorAdapter
{
    protected static  ?Field\Collection $staticFieldsCollection = null;
    protected int     $entityTypeId;
    protected Context $crmContext;

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldsCollection
     * @param array $dependantFieldsMap
     * @throws \Exception
     */
    public function __construct(Field\Collection $fieldsCollection, array $dependantFieldsMap = [])
    {
        static::$staticFieldsCollection = $fieldsCollection;
        $this->entityTypeId = Dynamic::getInstance()->getEntityTypeId();
        $this->crmContext   = Container::getInstance()->getContext();
        parent::__construct($fieldsCollection, $dependantFieldsMap);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\EO_Status_Collection $stages
     * @param array $componentParameters
     * @return \Vendor\Project\Dynamic\Service\EditorAdapter
     * @throws \Exception
     */
    public function processByItem(Item $item, EO_Status_Collection $stages, array $componentParameters = []): EditorAdapter
    {
        $this->crmContext->setItem($item);
        $this->markReadonlyFields($this->crmContext);
        $this->markHiddenFields($this->crmContext);
        return parent::processByItem($item, $stages, $componentParameters);
    }

    /**
     * @param array $userFields
     * @param array $visibilityConfig
     * @param int $entityTypeId
     * @param int $entityId
     * @param string $fileHandlerUrl
     * @return array
     */
    public static function prepareEntityUserFields(array $userFields, array $visibilityConfig, int $entityTypeId, int $entityId, string $fileHandlerUrl = ''): array
    {
        $userFields = [];
        if (static::$staticFieldsCollection !== null)
        {
            foreach (static::$staticFieldsCollection as $field)
            {
                if ($field->isUserField())
                {
                    $userField = $field->getUserField();
                    $userField['ATTRIBUTES'] = $field->getAttributes();
                    $userFields[$field->getName()] = $userField;
                }
            }
        }

        $preparedFields = parent::prepareEntityUserFields($userFields, $visibilityConfig, $entityTypeId, $entityId, $fileHandlerUrl);

        foreach ($preparedFields as $key => $field)
        {
            $srcField = array_key_exists($field['name'], $userFields) ? $userFields[$field['name']] : [];

            $editable = !empty($srcField)
                        && isset($srcField['EDIT_IN_LIST'])
                        && ($srcField['EDIT_IN_LIST'] === 'Y')
                        && !CCrmFieldInfoAttr::isFieldReadOnly($srcField);

            $preparedFields[$key]['enableAttributes'] = true;
            $preparedFields[$key]['editable'] = $editable;
        }

        return $preparedFields;
    }

    /**
     * @param \Vendor\Project\Dynamic\Service\Context $context
     * @return void
     * @throws \Exception
     */
    protected function markReadonlyFields(Context $context): void
    {
        FieldManager::getInstance($this->entityTypeId)->markReadonlyFieldsByContext(
            $this->fieldsCollection,
            $context
        );
    }

    /**
     * @param \Vendor\Project\Dynamic\Service\Context $context
     * @return void
     * @throws \Exception
     */
    protected function markHiddenFields(Context $context): void
    {
        FieldManager::getInstance($this->entityTypeId)->markHiddenFieldsByContext(
            $this->fieldsCollection,
            $context
        );
    }

    /*protected function processFieldsAttributes(array $fields, int $mode, Item $item): array
    {
        return parent::processFieldsAttributes($fields, $mode, $item);
    }

    protected function getEntityDataForEntityFields(Item $item, array $entityFields, array $entityData): array
    {
        return parent::getEntityDataForEntityFields($item, $entityFields, $entityData);
    }*/
}