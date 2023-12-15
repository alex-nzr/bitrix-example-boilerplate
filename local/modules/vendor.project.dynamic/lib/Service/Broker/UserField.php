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
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\UserFieldTable;
use CUserFieldEnum;
use Exception;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Service\Container;

/**
 * Класс по работе с пользовательскими полями
 * Методы связанные с кодом поля (get...ByCode()) работают только для смарт-процесса из текущего модуля,
 * так как на основе кода поля и typeId сущности получается FIELD_NAME.
 * @class UserField
 * @package Vendor\Project\Dynamic\Service\Broker
 */
class UserField extends Broker
{
    protected array $userFieldCollection = [];
    protected array $userFieldEnumCollection = [];
    protected int $typeId;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->typeId = Configuration::getInstance()->getTypeId();
    }

    /**
     * @param int $id
     * @return array|null
     * @throws \Exception
     */
    protected function loadEntry(int $id): ?array
    {
        if (!key_exists($id, $this->userFieldCollection))
        {
            $field = UserFieldTable::query()->where('ID', $id)->setSelect(['*'])->fetch();
            if (is_array($field) && !empty($field))
            {
                $this->userFieldCollection[$id] = $this->normalizeField($field);
                return $this->userFieldCollection[$id];
            }
        }

        return null;
    }

    /**
     * @param string $xmlId
     * @param string $entityId
     * @return array|null
     * @throws \Exception
     */
    protected function loadEntryByXmlId(string $xmlId, string $entityId): ?array
    {
        $fields = UserFieldTable::query()
            ->where('XML_ID', $xmlId)
            ->where('ENTITY_ID', $entityId)
            ->setSelect(['*'])
            ->fetchAll();

        if (count($fields) > 1)
        {
            throw new Exception("Entity $entityId has " . count($fields) . " fields with XML_ID = $xmlId");
        }

        $field = current($fields);

        if (is_array($field) && !empty($field))
        {
            $this->userFieldCollection[$field['ID']] = $this->normalizeField($field);
            return $this->userFieldCollection[$field['ID']];
        }

        return null;
    }

    /**
     * @param string $fieldName
     * @param string $entityId
     * @return array|null
     * @throws \Exception
     */
    protected function loadEntryByFieldName(string $fieldName, string $entityId): ?array
    {
        $field = UserFieldTable::query()
            ->where('FIELD_NAME', $fieldName)
            ->where('ENTITY_ID', $entityId)
            ->setSelect(['*'])
            ->fetch();

        if (is_array($field) && !empty($field))
        {
            $this->userFieldCollection[$field['ID']] = $this->normalizeField($field);
            return $this->userFieldCollection[$field['ID']];
        }

        return null;
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Exception
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
            else
            {
                $entries[$id] = $this->loadEntry($id);
            }
        }
        return $entries;
    }

    /**
     * Only for userFields of current dynamic entity
     * @param string $ufCode
     * @return string|null
     */
    public function getUfNameByCode(string $ufCode): ?string
    {
        return 'UF_CRM_' . $this->typeId . '_' . $ufCode;
    }

    /**
     * Only for userFields of current dynamic entity
     * @param string $ufCode
     * @param string $entityId
     * @return int|null
     * @throws \Exception
     */
    public function getUfIdByCode(string $ufCode, string $entityId): ?int
    {
        return $this->getUfIdByName($this->getUfNameByCode($ufCode), $entityId);
    }

    /**
     * Only for userFields of current dynamic entity
     * @param string $ufCode
     * @return string
     */
    public function getUfXmlIdByCode(string $ufCode): string
    {
        return Constants::UF_XML_ID_PREFIX . $ufCode;
    }

    /**
     * @param string $xmlId
     * @param string $entityId
     * @return string|null
     * @throws \Exception
     */
    public function getUfNameByXmlId(string $xmlId, string $entityId): ?string
    {
        static $cache = [];

        if (!array_key_exists($xmlId, $cache))
        {
            $field = null;
            foreach ($this->userFieldCollection as $item)
            {
                if ($item['XML_ID'] === $xmlId && $item['ENTITY_ID'] === $entityId)
                {
                    $field = $item;
                }
            }

            if (is_null($field))
            {
                $field = $this->loadEntryByXmlId($xmlId, $entityId);
            }

            $cache[$xmlId] = is_array($field) ? $field['FIELD_NAME'] : null;
        }

        return $cache[$xmlId];
    }

    /**
     * @param string $userFieldName
     * @param string $entityId
     * @return int|null
     * @throws \Exception
     */
    public function getUfIdByName(string $userFieldName, string $entityId): ?int
    {
        static $cache = [];

        if (!array_key_exists($userFieldName, $cache))
        {
            $field = null;
            foreach ($this->userFieldCollection as $item)
            {
                if ($item['FIELD_NAME'] === $userFieldName && $item['ENTITY_ID'] === $entityId)
                {
                    $field = $item;
                }
            }

            if (is_null($field))
            {
                $field = $this->loadEntryByFieldName($userFieldName, $entityId);
            }

            $cache[$userFieldName] = is_array($field) ? $field['ID'] : null;
        }

        return $cache[$userFieldName];
    }

    /**
     * @param int $fieldId
     * @return array
     * @throws \Exception
     */
    public function getUfListValuesByFieldId(int $fieldId): array
    {
        static $cache = [];

        if (!array_key_exists($fieldId, $cache))
        {
            $cache[$fieldId] = [];
            if (!array_key_exists($fieldId, $this->userFieldCollection))
            {
                $field = $this->loadEntry($fieldId);
            }
            else
            {
                $field = $this->userFieldCollection[$fieldId];
            }

            if (is_array($field) && is_array($field['LIST']))
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
     * @param string $entityId
     * @return array
     * @throws \Exception
     */
    public function getUfListValuesByFieldName(string $userFieldName, string $entityId): array
    {
        return $this->getUfListValuesByFieldId($this->getUfIdByName($userFieldName, $entityId));
    }

    /**
     * @param string $ufCode
     * @param string $entityId
     * @return array
     * @throws \Exception
     */
    public function getUfListValuesByFieldCode(string $ufCode, string $entityId): array
    {
        return $this->getUfListValuesByFieldName($this->getUfNameByCode($ufCode), $entityId);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getUfListValueById(int $id): string
    {
        if (!array_key_exists($id, $this->userFieldEnumCollection))
        {
            $this->userFieldEnumCollection[$id] = Container::getInstance()->getEnumerationBroker()->loadEntry($id);
        }

        return is_array($this->userFieldEnumCollection[$id]) ? (string)$this->userFieldEnumCollection[$id]['VALUE'] : '';
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getUfListXmlIdById(int $id): string
    {
        if (!array_key_exists($id, $this->userFieldEnumCollection))
        {
            $this->userFieldEnumCollection[$id] = Container::getInstance()->getEnumerationBroker()->loadEntry($id);
        }

        return is_array($this->userFieldEnumCollection[$id]) ? (string)$this->userFieldEnumCollection[$id]['XML_ID'] : '';
    }

    /**
     * @param string $xmlId
     * @param string $fieldName
     * @param string $entityId
     * @return int|null
     * @throws \Exception
     */
    public function getUfListIdByXmlId(string $xmlId, string $fieldName, string $entityId): ?int
    {
        $id = null;
        $field = $this->loadEntry((int)$this->getUfIdByName($fieldName, $entityId));

        if (is_array($field) && is_array($field['LIST']))
        {
            foreach ($field['LIST'] as $item) {
                if ($item['XML_ID'] === $xmlId)
                {
                    $id = (int)$item['ID'];
                    break;
                }
            }
        }
        return $id;
    }

    /**
     * @param string $fieldName
     * @param $value
     * @param string $entityId
     * @return int|null
     * @throws \Exception
     */
    public function getUfListIdByValue(string $fieldName, $value, string $entityId): ?int
    {
        $id = null;
        $field = $this->loadEntry((int)$this->getUfIdByName($fieldName, $entityId));

        if (is_array($field) && is_array($field['LIST']))
        {
            foreach ($field['LIST'] as $item) {
                if ($item['VALUE'] === $value)
                {
                    $id = (int)$item['ID'];
                    break;
                }
            }
        }

        return $id;
    }

    /**
     * @param int $fieldId
     * @return array
     * @throws \Exception
     */
    public function getUfListXmlIdsByFieldId(int $fieldId): array
    {
        $xmlIds = [];
        $field = $this->loadEntry($fieldId);

        if (is_array($field) && is_array($field['LIST']))
        {
            foreach ($field['LIST'] as $item)
            {
                $xmlIds[$item['ID']] = $item['XML_ID'];
            }
        }

        return $xmlIds;
    }

    /**
     * @param array $field
     * @return array
     */
    protected function normalizeField(array $field): array
    {
        if (key_exists('USER_TYPE_ID', $field) && ($field['USER_TYPE_ID'] === EnumType::USER_TYPE_ID))
        {
            $field['LIST'] = [];
            $enumItemResult = CUserFieldEnum::GetList([], [
                'USER_FIELD_ID' => $field['ID']
            ]);

            while($enumItem = $enumItemResult->GetNext())
            {
                $field['LIST'][$enumItem['ID']] = $enumItem;
                $this->userFieldEnumCollection[$enumItem['ID']] = $enumItem;
            }
        }
        return $field;
    }
}