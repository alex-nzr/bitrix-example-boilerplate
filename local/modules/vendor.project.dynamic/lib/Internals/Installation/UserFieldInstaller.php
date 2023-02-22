<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - UserFieldInstaller.php
 * 24.11.2022 18:18
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Exception;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Helper;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use CUserFieldEnum;
use CUserTypeEntity;

/**
 * Class UserFieldInstaller
 * @package Vendor\Project\Dynamic\Internals\Installation
 */
class UserFieldInstaller
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(): Result
    {
        global $APPLICATION;
        $result = new Result;
        $oUserTypeEntity = new CUserTypeEntity();

        $newFields = [];
        foreach (static::getFields() as $userField)
        {
            $ufRes = $oUserTypeEntity::GetList([], ['FIELD_NAME' => $userField['FIELD_NAME']]);
            if ($arField = $ufRes->Fetch())
            {
                $ufId = $arField['ID'];
                $updated = $oUserTypeEntity->Update($ufId, $userField);
                if (!$updated){
                    $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                }
                else
                {
                    if ($userField['USER_TYPE_ID'] === 'enumeration' && is_array($userField['LIST']))
                    {
                        $currentXmlIds = Helper\Main\UserField::getUfListXmlIdsByFieldId((int)$arField['ID']);

                        foreach ($userField['LIST'] as $key => $valueAr)
                        {
                            if (in_array($valueAr['XML_ID'], $currentXmlIds))
                            {
                                $enumId = array_search($valueAr['XML_ID'], $currentXmlIds);
                                $userField['LIST'][$enumId] = $valueAr;
                                unset(
                                    $currentXmlIds[array_search($valueAr['XML_ID'], $currentXmlIds)],
                                    $userField['LIST'][$key]
                                );
                            }
                        }

                        if (count($currentXmlIds) > 0)
                        {
                            foreach ($currentXmlIds as $enumId => $xmlId)
                            {
                                $userField['LIST'][$enumId] = [
                                    "DEL" => "Y",
                                ];
                            }
                        }

                        $obEnum = new CUserFieldEnum;
                        $enumSuccess = $obEnum->SetEnumValues($ufId, $userField['LIST']);
                        if(!$enumSuccess){
                            $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                        }
                    }
                }
            }
            else
            {
                $newFields[] = $userField;
            }
        }

        if (!empty($newFields))
        {
            $addRes = static::addUserFields($newFields);
            if(!$addRes->isSuccess()){
                $result->addErrors($addRes->getErrors());
            }
        }

        return $result;
    }

    /**
     * @param array $userFields
     * @return \Bitrix\Main\Result
     */
    protected static function addUserFields(array $userFields): Result
    {
        global $APPLICATION;
        $result = new Result;
        $oUserTypeEntity = new CUserTypeEntity();

        foreach ($userFields as $userField)
        {
            $ufId   = $oUserTypeEntity->Add($userField);
            if (!(int)$ufId > 0){
                $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
            }
            else
            {
                if ($userField['USER_TYPE_ID'] === 'enumeration' && is_array($userField['LIST']))
                {
                    $obEnum = new CUserFieldEnum;
                    $enumSuccess = $obEnum->SetEnumValues($ufId, $userField['LIST']);
                    if(!$enumSuccess){
                        $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getFields(): array
    {
        $userFields = static::getUserFieldsDescription();
        $preparedUserFields = [];

        $typeId        = Configuration::getInstance()->getTypeIdFromOption();
        if ($typeId <= 0)
        {
            throw new Exception('Error in '.__METHOD__.': typeId must be greater than 0');
        }

        $entityIdForUf = 'CRM_' . $typeId;
        $ufPrefix      = 'UF_CRM_' . $typeId . '_';

        foreach ($userFields as $key => $userField) {

            $userField['ENTITY_ID']     = $entityIdForUf;
            $userField['FIELD_NAME']    = $ufPrefix . $userField['FIELD_NAME'];
            $userField['XML_ID']        = $userField['FIELD_NAME'];
            $userField['SORT']          = $key > 0 ? $key * 10 : 10;
            $userField['SHOW_IN_LIST']  = 'N';//($userField['HIDDEN'] === 'Y') ? 'N' : 'Y';
            $userField['IS_SEARCHABLE'] = 'Y';

            if ($userField['HIDDEN'] === 'Y'){
                $userField['SHOW_FILTER'] = 'N';
            }
            else{
                $userField['SHOW_FILTER'] = ($userField['USER_TYPE_ID'] === 'string') ? 'S' : 'I';
            }

            $title = [
                'ru'    => $userField['TITLE_RU'],
                'en'    => $userField['TITLE_EN'],
            ];

            $userField['EDIT_FORM_LABEL'] = $title;
            $userField['LIST_COLUMN_LABEL'] = $title;
            $userField['LIST_FILTER_LABEL'] = $title;
            $userField['ERROR_MESSAGE']   = [
                'ru'    => 'ERROR ON FILLING ' . $userField['TITLE_RU'],
                'en'    => 'ERROR ON FILLING ' . $userField['TITLE_EN'],
            ];
            $userField['HELP_MESSAGE']   = ['ru'    => '', 'en'    => ''];

            unset($userField['TITLE_RU'], $userField['TITLE_EN'], $userField['HIDDEN']);

            if (is_array($userField['LIST']))
            {
                $userField['LIST'] = static::prepareUserFieldEnumData($userField['FIELD_NAME'], $userField['LIST']);
            }

            $preparedUserFields[] = $userField;
        }
        return $preparedUserFields;
    }

    /**
     * @return array
     */
    protected static function getUserFieldsDescription(): array
    {
        return [
            [
                'TITLE_RU'     => 'String field',
                'TITLE_EN'     => 'String field',
                'FIELD_NAME'   => 'EXAMPLE_STRING',
                'USER_TYPE_ID' => 'string_formatted',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'PATTERN' => '#VALUE#'
                ]
            ],
            [
                'TITLE_RU'     => 'Example list',
                'TITLE_EN'     => "Example list",
                'FIELD_NAME'   => 'EXAMPLE_LIST',
                'USER_TYPE_ID' => 'enumeration',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DISPLAY'          => 'DIALOG',
                    'LIST_HEIGHT'      => 5,
                    'SHOW_NO_VALUE'    => 'Y',
                ],
                'LIST'         => [
                    //'xmlId1' => 'Value 1', //test deleting
                    'xmlId2' => 'Value 2',
                    'xmlId3' => 'Value 3 - changed',
                    'xmlId4' => 'Value 4',
                ]
            ],
            [
                'TITLE_RU'     => 'Example date',
                'TITLE_EN'     => 'Example date',
                'FIELD_NAME'   => 'EXAMPLE_DATE',
                'USER_TYPE_ID' => 'date',
                'MULTIPLE'     => 'N',
                'MANDATORY'    => 'N',
                'EDIT_IN_LIST' => '',
                'SETTINGS'     => [
                    'DEFAULT_VALUE' => new Date()
                ]
            ],
        ];
    }

    /**
     * @param string $fieldName
     * @param array $values
     * @return array
     */
    protected static function prepareUserFieldEnumData(string $fieldName, array $values): array
    {
        $arAddEnum = [];
        $counter = 0;
        foreach ($values as $xmlId => $value)
        {
            if (empty($xmlId) || (intval($xmlId) === $counter))
            {
                $xmlId = $fieldName.'_'.$counter;
            }
            $arAddEnum['n'.$counter] = [
                'XML_ID' => $xmlId,
                'VALUE' => $value,
                'DEF' => 'N',
                'SORT' => $counter > 0 ? $counter * 10 : 10
            ];
            $counter++;
        }
        return $arAddEnum;
    }
}