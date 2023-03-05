<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - UserField.php
 * 03.03.2023 02:32
 * ==================================================
 */


namespace Vendor\Project\Dynamic\Service\Broker;

use Bitrix\Crm\Service\Broker;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\UserField\Types\EnumType;
use CUserFieldEnum;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Service\Container;

/**
 * @class UserField
 * @package Vendor\Project\Dynamic\Service\Broker
 */
class UserField extends Broker
{
    protected ?Factory $factory;
    protected array $userFieldCollection = [];
    protected array $userFieldEnumCollection = [];


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->factory = Container::getInstance()->getFactory(Configuration::getInstance()->getEntityTypeId());

        $fields = $this->factory->getUserFields();
        foreach ($fields as $field)
        {
            if ($field['USER_TYPE_ID'] === EnumType::USER_TYPE_ID)
            {
                $field['LIST'] = [];
                $enumItemResult = CUserFieldEnum::GetList([], [
                    "USER_FIELD_ID" => $field['ID']
                ]);

                while($enumItem = $enumItemResult->GetNext())
                {
                    $field['LIST'][$enumItem["ID"]] = $enumItem;
                    $this->userFieldEnumCollection[$enumItem["ID"]] = $enumItem;
                }
            }
            $this->userFieldCollection[$field['ID']] = $field;
        }
    }

    /**
     * @param int $id
     * @return array|null
     */
    protected function loadEntry(int $id): ?array
    {
        return array_key_exists($id, $this->userFieldCollection) ? $this->userFieldCollection[$id] : null;
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function loadEntries(array $ids): array
    {
        $entries = [];
        foreach ($ids as $id)
        {
            if (array_key_exists($id, $this->userFieldCollection))
            {
                $entries[$id] = $this->userFieldCollection[$id];
            }
        }
        return $entries;
    }

    /**
     * @param string $ufCode
     * @return string|null
     */
    public function getUfNameByCode(string $ufCode): ?string
    {
        return $this->getUfNameByXmlId($this->getUfXmlIdByCode($ufCode));
    }

    /**
     * @param string $ufCode
     * @return int|null
     */
    public function getUfIdByCode(string $ufCode): ?int
    {
        return $this->getUfIdByName($this->getUfNameByCode($ufCode));
    }

    /**
     * @param string $ufCode
     * @return string
     */
    public function getUfXmlIdByCode(string $ufCode): string
    {
        return Constants::UF_XML_ID_PREFIX . $ufCode;
    }

    /**
     * @param string $xmlId
     * @return string|null
     */
    public function getUfNameByXmlId(string $xmlId): ?string
    {
        static $cache = [];

        if (!array_key_exists($xmlId, $cache))
        {
            $cache[$xmlId] = null;
            foreach ($this->userFieldCollection as $field)
            {
                if ($field['XML_ID'] === $xmlId)
                {
                    $cache[$xmlId] = $field['FIELD_NAME'];
                }
            }
        }

        return $cache[$xmlId];
    }

    /**
     * @param string $userFieldName
     * @return int|null
     */
    public function getUfIdByName(string $userFieldName): ?int
    {
        static $cache = [];

        if (!array_key_exists($userFieldName, $cache))
        {
            $cache[$userFieldName] = null;
            foreach ($this->userFieldCollection as $field)
            {
                if ($field['FIELD_NAME'] === $userFieldName)
                {
                    $cache[$userFieldName] = $field['ID'];
                }
            }
        }

        return $cache[$userFieldName];
    }

    /**
     * @param int $fieldId
     * @return array
     */
    public function getUfListValuesByFieldId(int $fieldId): array
    {
        static $cache = [];

        if (!array_key_exists($fieldId, $cache))
        {
            $cache[$fieldId] = [];
            if (array_key_exists($fieldId, $this->userFieldCollection) && is_array($this->userFieldCollection[$fieldId]['LIST']))
            {
                foreach ($this->userFieldCollection[$fieldId]['LIST'] as $item) {
                    $cache[$fieldId][$item['ID']] = $item['VALUE'];
                }
            }
        }

        return $cache[$fieldId];
    }

    /**
     * @param string $userFieldName
     * @return array
     */
    public function getUfListValuesByFieldName(string $userFieldName): array
    {
        return $this->getUfListValuesByFieldId($this->getUfIdByName($userFieldName));
    }

    /**
     * @param string $ufCode
     * @return array
     */
    public function getUfListValuesByFieldCode(string $ufCode): array
    {
        return $this->getUfListValuesByFieldName($this->getUfNameByCode($ufCode));
    }

    /**
     * @param int $id
     * @return string
     */
    public function getUfListValueById(int $id): string
    {
        if (array_key_exists($id, $this->userFieldEnumCollection))
        {
            return (string)$this->userFieldEnumCollection[$id]["VALUE"];
        }
        return '';
    }

    /**
     * @param int $id
     * @return string
     */
    public function getUfListXmlIdById(int $id): string
    {
        if (array_key_exists($id, $this->userFieldEnumCollection))
        {
            return (string)$this->userFieldEnumCollection[$id]["XML_ID"];
        }
        return '';
    }

    /**
     * @param string $xmlId
     * @return int|null
     */
    public function getUfListIdByXmlId(string $xmlId): ?int
    {
        $id = null;
        foreach ($this->userFieldEnumCollection as $item) {
            if ($item['XML_ID'] === $xmlId)
            {
                $id = (int)$item['ID'];
            }
        }
        return $id;
    }

    /**
     * @param string $fieldName
     * @param $value
     * @return int|null
     */
    public function getUfListIdByValue(string $fieldName, $value): ?int
    {
        $id = null;
        $fieldId = $this->getUfIdByName($fieldName);
        if (array_key_exists($fieldId, $this->userFieldCollection))
        {
            if (is_array($this->userFieldCollection[$fieldId]['LIST']))
            {
                foreach ($this->userFieldCollection[$fieldId]['LIST'] as $enumItem) {
                    if ($enumItem['VALUE'] === $value)
                    {
                        $id = intval($enumItem['ID']);
                        break;
                    }
                }
            }
        }
        return $id;
    }

    /**
     * @param int $fieldId
     * @return array
     */
    public function getUfListXmlIdsByFieldId(int $fieldId): array
    {
        $xmlIds = [];
        foreach ($this->userFieldEnumCollection as $enumItem)
        {
            if ((int)$enumItem['USER_FIELD_ID'] === $fieldId)
            {
                $xmlIds[$enumItem['ID']] = $enumItem["XML_ID"];
            }
        }
        return $xmlIds;
    }
}