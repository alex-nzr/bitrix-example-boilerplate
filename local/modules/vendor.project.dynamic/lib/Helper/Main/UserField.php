<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - UserField.php
 * 25.11.2022 01:12
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Helper\Main;

use CUserFieldEnum;
use CUserTypeEntity;

/**
 * Class UserField
 * @package Vendor\Project\Dynamic\Helper
 */
class UserField
{
    /**
     * @param string $code
     * @return int|null
     */
    public static function getUserFieldIdByCode(string $code): ?int
    {
        $rsData = CUserTypeEntity::GetList([], ['FIELD_NAME' => $code]);
        if($arRes = $rsData->Fetch())
        {
            return (int)$arRes['ID'];
        }
        return null;
    }

    /**
     * @param int $userFieldId
     * @return array
     */
    public static function getUfListValuesByFieldId(int $userFieldId): array
    {
        $filter = [
            "USER_FIELD_ID" => $userFieldId
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["VALUE"];
        }
        return $values;
    }

    /**
     * @param int $userFieldId
     * @return array
     */
    public static function getUfListXmlIdsByFieldId(int $userFieldId): array
    {
        $filter = [
            "USER_FIELD_ID" => $userFieldId
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["XML_ID"];
        }
        return $values;
    }

    /**
     * @param string $userFieldCode
     * @return array
     */
    public static function getUfListValuesByFieldCode(string $userFieldCode): array
    {
        $filter = [
            "USER_FIELD_ID" => static::getUserFieldIdByCode($userFieldCode)
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["VALUE"];
        }
        return $values;
    }

    /**
     * @param $id
     * @return string
     */
    public static function getUfListValueById($id): string
    {
        $value = '';
        if (!empty($id))
        {
            $userField = CUserFieldEnum::GetList([], ["ID" => (int)$id]);
            if($userFieldAr = $userField->GetNext())
            {
                $value =  $userFieldAr["VALUE"];
            }
        }
        return $value;
    }

    /**
     * @param string $userFieldCode
     * @param $value
     * @return int|null
     */
    public static function getUfListIdByValue(string $userFieldCode, $value): ?int
    {
        $id = null;

        if (!empty($value))
        {
            $filter = [
                "VALUE" => $value,
                "USER_FIELD_ID" => static::getUserFieldIdByCode($userFieldCode)
            ];
            $userField = CUserFieldEnum::GetList([], $filter);

            if($userFieldAr = $userField->GetNext())
            {
                $id = (int)$userFieldAr["ID"];
            }
        }
        return $id;
    }

    /**
     * @param $id
     * @return string
     */
    public static function getUfListXmlIdById($id): string
    {
        $xmlId = '';
        if (!empty($id))
        {
            $userField = CUserFieldEnum::GetList([], ["ID" => (int)$id]);
            if($userFieldAr = $userField->GetNext())
            {
                $xmlId = $userFieldAr["XML_ID"];
            }
        }
        return $xmlId;
    }
}