<?php
namespace Vendor\Project\Routing\Service\IBlock;

use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use CIBlockElement;
use CUtil;
use Exception;

class IBlockElementService
{
    public static function iBlockElementGetById($id): array
    {
        try {
            Loader::includeModule('iblock');

            $elRes = CIBlockElement::GetByID($id);
            if ($el = $elRes->GetNextElement()){
                $arFields = $el->GetFields();
                $arFields['PROPERTIES'] = $el->GetProperties();
                return $arFields;
            }
            else{
                throw new Exception("Element with id - $id not found");
            }
        }
        catch (Exception $e){
            return [
                'error' => $e->getCode(),
                'error_description' => $e->getMessage()
            ];
        }
    }

    public static function iBlockElementAdd(Request $request): array
    {
        try
        {
            Loader::includeModule('iblock');

            $fields = $request->getPostList()->toArray();
            if (!isset($fields['iblockId']))
            {
                throw new Exception("'iblockId' is required but empty");
            }

            $arFields = self::buildArFields($fields);

            $el = new CIBlockElement;
            if($elId = $el->Add($arFields))
                return array('result' => $elId);
            else{
                throw new Exception('ERROR_ON_ADD - ' . $el->LAST_ERROR);
            }
        }
        catch (Exception $e){
            return [
                'error' => $e->getCode(),
                'error_description' => $e->getMessage()
            ];
        }
    }

    public static function iBlockElementUpdate(Request $request): array
    {
        try
        {
            Loader::includeModule('iblock');

            $fields = $request->getPostList()->toArray();
            if (!isset($fields['id']))
            {
                throw new Exception("'id' is required but empty");
            }

            $arFields = self::buildArFields($fields);

            $el = new CIBlockElement;
            if (is_array($arFields['PROPERTY_VALUES'])){
                CIBlockElement::SetPropertyValuesEx($fields['id'], false, $arFields['PROPERTY_VALUES']);
            }
            unset($arFields['PROPERTY_VALUES']);
            $el->Update($fields['id'], $arFields);

            if(empty($el->LAST_ERROR))
                return array('result' => true);
            else{
                throw new Exception('ERROR_ON_UPDATE - ' . $el->LAST_ERROR);
            }
        }
        catch (Exception $e){
            return [
                'error' => $e->getCode(),
                'error_description' => $e->getMessage()
            ];
        }
    }

    public static function iBlockElementDelete($id): array
    {
        try
        {
            Loader::includeModule('iblock');

            if (empty($id) || ((int)$id <= 0))
            {
                throw new Exception("'id' is required but empty");
            }

            if(CIBlockElement::Delete($id))
                return ['result' => true];
            else{
                throw new Exception('ERROR_ON_DELETING');
            }
        }
        catch (Exception $e){
            return [
                'error' => $e->getCode(),
                'error_description' => $e->getMessage()
            ];
        }
    }

    protected static function buildArFields($postFields): array
    {
        global $USER;
        $arFields = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_ID"   => (int)$postFields['iblockId'],
        ];

        if (array_key_exists('DETAIL_PICTURE', $postFields)){
            $arFields["DETAIL_PICTURE"] = self::makeFileArrayFromBase64($postFields['DETAIL_PICTURE']);
        }

        if (!array_key_exists('CODE', $postFields) && !isset($postFields['id'])){
            $arFields['CODE'] = Cutil::translit($postFields['NAME'],"ru");
        }
        return array_merge($postFields, $arFields);
    }

    protected static function prepareElementPropsShort(array $fetchResult): array
    {
        $props = [];
        foreach($fetchResult as $prop)
        {
            if (empty($props[$prop["IBLOCK_PROPERTY_ID"]]))
            {
                $props[$prop["IBLOCK_PROPERTY_ID"]] = $prop;
            }
            else
            {
                if (!empty($props[$prop["IBLOCK_PROPERTY_ID"]]["ID"]))
                {
                    //if single element then we push it into array and add next element
                    $props[$prop["IBLOCK_PROPERTY_ID"]] = [ $props[$prop["IBLOCK_PROPERTY_ID"]], $prop ];
                }
                else
                {
                    $props[$prop["IBLOCK_PROPERTY_ID"]][] = $prop;
                }
            }
        }
        return $props;
    }

    protected static function prepareElementProps(array $fetchResult): array
    {
        $props = [];
        foreach($fetchResult as $prop)
        {
            if (empty($props[$prop["IBLOCK_PROPERTY_ID"]]))
            {
                $props[$prop["IBLOCK_PROPERTY_ID"]] = $prop;
            }
            else
            {
                if (is_array($props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE"]))
                {
                    $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE"][] = $prop["VALUE"];
                    $props[$prop["IBLOCK_PROPERTY_ID"]]["ID"][] = $prop["ID"];

                    if (is_array($props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"])){
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"][] = $prop["VALUE_ENUM"];
                    }
                    if (is_array($props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"])){
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"][] = $prop["VALUE_NUM"];
                    }
                }
                else
                {
                    $props[$prop["IBLOCK_PROPERTY_ID"]]["ID"] = [
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["ID"],
                        $prop["ID"]
                    ];
                    $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE"] = [
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE"],
                        $prop["VALUE"]
                    ];

                    if (!empty($props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"])){
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"] = [
                            $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"],
                            $prop["VALUE_ENUM"]
                        ];
                    }
                    if (!empty($props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"])){
                        $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"] = [
                            $props[$prop["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"],
                            $prop["VALUE_NUM"]
                        ];
                    }
                }
            }
        }
        return $props;
    }

    protected static function makeFileArrayFromBase64($imgInBase64){
        //$fileName = save file from $imgInBase64
        //return CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/path_to_file/".$fileName)
        return null;
    }
}