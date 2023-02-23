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
use Vendor\Project\Dynamic\Internals\Control\EventManager;
use Vendor\Project\Dynamic\Internals\EditorConfig;
use Vendor\Project\Dynamic\Service\EntityEditor\FieldManager;

/**
 * @class EditorAdapter
 * @package Vendor\Project\Dynamic\Service
 */
class EditorAdapter extends \Bitrix\Crm\Service\EditorAdapter
{
    public const FIELD_OPPORTUNITY = 'OPPORTUNITY_WITH_CURRENCY';
    public const FIELD_CLIENT = 'CLIENT';
    public const FIELD_CLIENT_DATA_NAME = 'CLIENT_DATA';
    public const FIELD_PRODUCT_ROW_SUMMARY = 'PRODUCT_ROW_SUMMARY';

    protected static  ?Field\Collection $staticFieldsCollection = null;
    protected int     $entityTypeId;
    protected Context $crmContext;
    protected int $typeId;

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldsCollection
     * @param array $dependantFieldsMap
     * @throws \Exception
     */
    public function __construct(Field\Collection $fieldsCollection, array $dependantFieldsMap = [])
    {
        static::$staticFieldsCollection = $fieldsCollection;
        parent::__construct($fieldsCollection, $dependantFieldsMap);
    }

    /**
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId(int $entityTypeId): static
    {
        $this->entityTypeId = $entityTypeId;
        return $this;
    }

    /**
     * @param int $typeId
     * @return $this
     */
    public function setTypeId(int $typeId): static
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @param \Vendor\Project\Dynamic\Service\Context $crmContext
     * @return $this
     */
    public function setCrmContext(Context $crmContext): static
    {
        $this->crmContext = $crmContext;
        return $this;
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
        $categoryCode = Dynamic::getInstance()->getCategoryCodeById($item->getCategoryId());
        $editorConfig = EditorConfig\Factory::getInstance($this->typeId, $this->entityTypeId)->createConfig($categoryCode);
        $this->markReadonlyFields($editorConfig);
        $this->markHiddenFields($editorConfig);
        $this->processAdditionalFields($editorConfig);
        EventManager::sendEntityDetailsContextReadyEvent();
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
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig $config
     * @return void
     */
    protected function markReadonlyFields(IEditorConfig $config): void
    {
        FieldManager::getInstance($this->entityTypeId)->markReadonlyFieldsByConfig(
            $this->fieldsCollection,
            $config
        );
    }

    /**
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig $config
     * @return void
     */
    protected function markHiddenFields(IEditorConfig $config): void
    {
        FieldManager::getInstance($this->entityTypeId)->markHiddenFieldsByConfig(
            $this->fieldsCollection,
            $config
        );
    }

    /**
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig|null $config
     * @return void
     */
    protected function processAdditionalFields(?IEditorConfig $config): void
    {
        $this->additionalFields = FieldManager::getInstance($this->entityTypeId)->markAdditionalFieldsByConfig(
            $this->additionalFields,
            $config
        );
    }

    /**
     * @param string $fieldCaption
     * @return void
     */
    public function addClientField(string $fieldCaption): void
    {
        $this->addEntityField(
            static::getClientField(
                $fieldCaption,
                static::FIELD_CLIENT,
                static::FIELD_CLIENT_DATA_NAME,
                ['entityTypeId' => $this->entityTypeId]
            )
        );
    }

    /**
     * @param string $fieldCaption
     * @param bool $isPaymentsEnabled
     * @return void
     */
    public function addOpportunityField(string $fieldCaption, bool $isPaymentsEnabled): void
    {
        $this->addEntityField(
            static::getOpportunityField($fieldCaption, static::FIELD_OPPORTUNITY, $isPaymentsEnabled)
        );
    }

    /**
     * @param string $fieldCaption
     * @return void
     */
    public function addProductRowSummaryField(string $fieldCaption): void
    {
        $this->addEntityField(static::getProductRowSummaryField($fieldCaption));
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