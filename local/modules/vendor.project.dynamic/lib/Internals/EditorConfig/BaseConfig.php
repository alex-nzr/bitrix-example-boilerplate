<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - BaseConfig.php
 * 19.02.2023 16:30
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\EditorConfig;

use Vendor\Project\Dynamic\Internals\Contract\IEditorConfig;
use Vendor\Project\Dynamic\Service\Broker;
use Vendor\Project\Dynamic\Service\Container;

/**
 * Class BaseConfig
 * @package Vendor\Project\Dynamic\Internals\EditorConfig
 */
abstract class BaseConfig implements IEditorConfig
{
    protected array $entityFields;
    protected int   $typeId;
    protected Broker\UserField $ufBroker;

    /**
     * @param int $typeId
     * @param int $entityTypeId
     * @throws \Exception
     */
    public function __construct(int $typeId, int $entityTypeId)
    {
        $this->typeId       = $typeId;
        $this->entityFields = Container::getInstance()->getFactory($entityTypeId)->getFieldsCollection()->toArray();
        $this->ufBroker     = Container::getInstance()->getUserFieldBroker();
    }

    /**
     * @return array[]
     */
    final public function getEditorConfigTemplate(): array
    {
        return $this->makeCompatible($this->getConfigScheme());
    }

    /**
     * @return array
     */
    abstract protected function getConfigScheme(): array;

    /**
     * @param array $data
     * @return array[]
     */
    protected function makeCompatible(array $data): array
    {
        $compatibleData = [];

        foreach ($data as $section)
        {
            if (is_array($section) && key_exists('type', $section) && $section['type'] === 'section')
            {
                $compatibleData[] = $section;
            }
        }

        return [
            [
                'name'     => 'default_column',
                'type'     => 'column',
                'elements' => $compatibleData,
            ]
        ];
    }

    /**
     * @return array
     */
    public function getHiddenFields(): array
    {
        $configFieldCodes = $this->getFieldCodesFromScheme();
        $itemFieldCodes   = array_keys($this->entityFields);

        return array_diff($itemFieldCodes, $configFieldCodes);
    }

    /**
     * @return array
     */
    public function getReadonlyFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getFieldCodesFromScheme(): array
    {
        $fieldCodes = [];

        foreach ($this->getConfigScheme() as $section)
        {
            if (is_array($section) && key_exists('type', $section) && $section['type'] === 'section')
            {
                if (key_exists('elements', $section) && is_array($section['elements']))
                {
                    foreach ($section['elements'] as $element)
                    {
                        if (is_array($element) && key_exists('name', $element) && !empty($element['name']))
                        {
                            $fieldCodes[] = $element['name'];
                        }
                    }
                }
            }
        }

        return $fieldCodes;
    }
}
