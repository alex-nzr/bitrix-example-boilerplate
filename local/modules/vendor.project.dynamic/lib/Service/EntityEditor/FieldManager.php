<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - FieldManager.php
 * 19.02.2023 13:07
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service\EntityEditor;

use Bitrix\Crm\Attribute\Entity\FieldAttributeTable;
use Bitrix\Crm\Attribute\FieldAttributeManager;
use Bitrix\Crm\Attribute\FieldAttributePhaseGroupType;
use Bitrix\Crm\Attribute\FieldAttributeType;
use Bitrix\Crm\Field\Collection;
use CCrmFieldInfoAttr;
use Exception;
use Vendor\Project\Dynamic\Entity\Dynamic;
use Vendor\Project\Dynamic\Internals\Contract\IEditorConfig;
use Vendor\Project\Dynamic\Internals\EditorConfig;
use Vendor\Project\Dynamic\Service\Container;
use Vendor\Project\Dynamic\Service\Context;

/**
 * @class FieldManager
 * @package Vendor\Project\Dynamic\Service\EntityEditor
 */
class FieldManager
{
    private static array $instances = [];
    private int $entityTypeId;

    /**
     * FieldManager constructor
     * @param int $entityTypeId
     */
    private function __construct(int $entityTypeId)
    {
        $this->entityTypeId = $entityTypeId;
    }

    /**
     * @param int $entityTypeId
     * @return \Vendor\Project\Dynamic\Service\EntityEditor\FieldManager|null
     */
    public static function getInstance(int $entityTypeId): ?FieldManager
    {
        if (!array_key_exists($entityTypeId, static::$instances))
        {
            static::$instances[$entityTypeId] = new static($entityTypeId);
        }
        return static::$instances[$entityTypeId];
    }

    /**
     * @param string $requiredFieldName
     * @param int $categoryId
     * @param array $fieldConfig
     * @return void
     * @throws \Exception
     */
    public function saveFieldAsRequired(string $requiredFieldName, int $categoryId, array $fieldConfig): void
    {
        $entityScope = 'category_' . $categoryId;

        //FieldAttributePhaseGroupType::ALL - для всех стадий и воронок
        //FieldAttributePhaseGroupType::PIPELINE - для успешной стадии и группы стадий "В работе"
        //FieldAttributePhaseGroupType::JUNK - для негативных стадий
        //Если поле обязательное для разных групп стадий, то создаётся несколько вложенных массивов
        // в массиве 'groups', каждый со своим 'phaseGroupTypeId'
        // если стадии идут подряд, то указывается только первая и последняя
        //если не подряд, то создаётся несколько элементов в 'items'
        //Для негативных стадий на каждую создаётся отдельный элемент в 'items', независимо от порядка следования
        /*[
            'typeId' => FieldAttributeType::REQUIRED,
            'groups' => [
                [
                    'phaseGroupTypeId' => FieldAttributePhaseGroupType::ALL
                ],
                [
                    'phaseGroupTypeId' => FieldAttributePhaseGroupType::PIPELINE,
                    'items' => [
                        [
                            'startPhaseId' => 'DT189_8:NEW',
                            'finishPhaseId' => 'DT189_8:PROCESS',
                        ],
                        [
                            'startPhaseId' => 'DT189_8:SUCCESS',
                            'finishPhaseId' => 'DT189_8:SUCCESS',
                        ]
                    ]
                ],
                [
                    'phaseGroupTypeId' => FieldAttributePhaseGroupType::JUNK,
                    'items' => [
                        [
                            'startPhaseId' => 'DT189_8:FAIL',
                            'finishPhaseId' => 'DT189_8:FAIL',
                        ]
                    ]
                ]
            ]
        ]*/
        FieldAttributeManager::saveEntityConfiguration(
            [
                'typeId' => FieldAttributeType::REQUIRED,
                'groups' => $fieldConfig
            ],
            $requiredFieldName,
            $this->entityTypeId,
            $entityScope
        );
    }

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldCollection
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig|null $config
     * @return void
     */
    public function markReadonlyFieldsByConfig(Collection $fieldCollection, ?IEditorConfig $config = null): void
    {
        if (!is_null($config))
        {
            $readonlyFields = $config->getReadonlyFields();

            foreach ($fieldCollection as $field)
            {
                if (in_array($field->getName(), $readonlyFields))
                {
                    $field->setAttributes(
                        array_unique(array_merge($field->getAttributes(), [CCrmFieldInfoAttr::ReadOnly]))
                    );
                }
            }
        }
    }

    /**
     * @param \Bitrix\Crm\Field\Collection $fieldCollection
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig|null $config
     * @return void
     */
    public function markHiddenFieldsByConfig(Collection $fieldCollection, ?IEditorConfig $config = null): void
    {
        if (!is_null($config))
        {
            $hiddenFields = $config->getHiddenFields();

            foreach ($fieldCollection as $field)
            {
                if (in_array($field->getName(), $hiddenFields))
                {
                    $field->setAttributes(
                        array_unique(array_merge($field->getAttributes(), [CCrmFieldInfoAttr::NotDisplayed]))
                    );
                }
            }
        }
    }

    /**
     * Check only readonly attr, because required and hidden already works
     * @param array $additionalFields
     * @param \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig|null $config
     * @return array
     */
    public function markAdditionalFieldsByConfig(array $additionalFields, ?IEditorConfig $config): array
    {
        $preparedFields = $additionalFields;
        foreach ($config->getReadonlyFields() as $readonlyField)
        {
            if (array_key_exists($readonlyField, $preparedFields) && is_array($preparedFields[$readonlyField]))
            {
                $preparedFields[$readonlyField]['editable'] = false;
            }
        }
        return $preparedFields;
    }

    private function __clone(){}
    public function __wakeup(){}
}