<?php
/**
 * @var CMain $APPLICATION
 */

use Bitrix\Main\Web\HttpClient;

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная");?>

    <h1><?php $APPLICATION->ShowTitle(false);?></h1>
<?php
$httpClient = new HttpClient([
    "disableSslVerification" => true,
]);
$postData = [
    //'iblockId' => 4,
    'id' => 375,
    'NAME' => "Deactivated ELEMENT",
    'PROPERTY_VALUES' => [
            'TEST_STRING' => " adas das das das "
    ],
    'DETAIL_TEXT' => "testing of CODE transliteration",
    'PREVIEW_TEXT' => "testing of CODE transliteration",
    'SORT' => "50",
    'ACTIVE' => "N",
    'CODE' => "deactivated",
    'XML_ID' => "aks-asj-dlk-asj-dal-sdj-lak-sd"
];
//$httpClient->post('https://'.$_SERVER['HTTP_HOST'].'/api/iblock/element/add/', $postData);
?>
    <pre>1 <?//print_r($httpClient->getStatus())?></pre>
    <pre>2 <?//print_r($httpClient->getResult())?></pre>
    <pre>3 <?//print_r($httpClient->getError())?></pre>
<?php
//$httpClient->get('https://'.$_SERVER['HTTP_HOST'].'/api/iblock/element/get/8');
?>
    <pre>1 <?//print_r($httpClient->getStatus())?></pre>
    <pre>2 <?//print_r(json_decode($httpClient->getResult(), true))?></pre>
    <pre>3 <?//print_r($httpClient->getError())?></pre>

<?php
//$httpClient->post('https://'.$_SERVER['HTTP_HOST'].'/api/iblock/element/update/', $postData);
?>
    <pre>1 <?//print_r($httpClient->getStatus())?></pre>
    <pre>2 <?//print_r(json_decode($httpClient->getResult(), true))?></pre>
    <pre>3 <?//print_r($httpClient->getError())?></pre>

<?php
//$httpClient->post('https://'.$_SERVER['HTTP_HOST'].'/api/iblock/element/delete/376');
?>
    <pre>1 <?//print_r($httpClient->getStatus())?></pre>
    <pre>2 <?//print_r(json_decode($httpClient->getResult(), true))?></pre>
    <pre>3 <?//print_r($httpClient->getError())?></pre>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');