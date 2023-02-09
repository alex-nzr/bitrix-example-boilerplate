<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$this->addExternalCss($componentPath . "/jcrop/jcrop.css");
$this->addExternalJS($componentPath . "/jcrop/jcrop.js");
?>

<div class="avatar" id="avatar-create-wrapper">
    <?if(count($arResult["ERRORS"])>0):?>
        <div id="avatar-errors">
            <?foreach($arResult["ERRORS"] as $error):?>
                <p class="avatar-errors-error"><?=$error?></p>
            <?endforeach;?>
        </div>
    <?endif;?>
    
    <div class="avatar-preview">
        <div id="avatar-preview-left-block" class="avatar-preview-left">
            <?if($arResult["SHOW_DEFAULT"]):?>
                <p id="avatar-preview-left-hint">
                    <span>
                        <?=Loc::getMessage("MAX_FILE_SIZE", ["#MAXFSIZE#"=>round((int)$arResult["MAX_FILE_SIZE"]/(1024*1024))]);?>
                    </span>
                </p>
            
                <form action="<?=$APPLICATION->GetCurPage(true);?>" id="avatar-upload-form" class="avatar-preview-form" method="POST" enctype="multipart/form-data" >
                    <label class="avatar-preview-upload" for="avatar-preview-upload-file-input">
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?=$arResult["MAX_FILE_SIZE"];?>"/>
                        <input 
                            id="avatar-preview-upload-file-input" 
                            type="file" 
                            name="<?=$arResult['FILE_INPUT_NAME']?>" 
                            accept="image/jpeg,image/png,image/jpg"
                            required
                        >
                        <img id="avatar-preview-upload-image" class="avatar-preview-upload-image" src="<?=$arResult["PREVIEW_PICTURE_SRC"]?>">
                    </label>
                    <input type="hidden" name="action" value="upload">
                    <?=bitrix_sessid_post()?>
                </form>
            <?else:?>
                <img id="avatar-preview-image" class="avatar-preview-image" src="<?=$arResult["PREVIEW_PICTURE_SRC"]?>">
            <?endif;?>
        </div>      

        <div id="avatar-preview-right-block" class="avatar-preview-right">
            
            <p id="avatar-preview-right-hint"><?=Loc::getMessage("CHOOSE_MASK")?></p>

            <form id="avatar-filters-form" action="<?=$APPLICATION->GetCurPage(true);?>" method="POST">
                <div class="avatar-preview-filters">
                    <?foreach($arResult["WATERMARKS"] as $key => $filter):?>
                        <label class="avatar-preview-filters-label" for="filter<?=$key?>">
                            <?if($arResult["USE_AJAX"] || $arResult["CROPPED"]):?>
                                <input type="radio" name="filter" id="filter<?=$key?>" value="<?=$filter;?>" required>
                            <?endif;?>
                            <img class="avatar-preview-filters-label-image" src="<?=$filter;?>">
                        </label>
                    <?endforeach;?>
                </div>
                <input type="hidden" id="clean_src" name="src" value="<?=$arResult["CLEAN_PICTURE_SRC"]?>">
                <input type="hidden" id="filtered_src" name="filt_src" 
                    value="<?if(!$arResult["SHOW_DEFAULT"]){echo $arResult["PREVIEW_PICTURE_SRC"];}?>">
                <input type="hidden" name="action" value="apply-filter">
            </form>

            <?if($arResult["FILTERED"]):?>
                <a class="avatar-btn avatar-btn-download" href="<?=$arResult["PREVIEW_PICTURE_SRC"]?>" download>
                    <?=Loc::getMessage("DOWNLOAD_BTN_TEXT")?>
                </a>

                <a class="avatar-btn avatar-btn-clean" href="<?=$APPLICATION->GetCurPage()?>">
                    <?=Loc::getMessage("CLEAN_BTN_TEXT")?>
                </a>
            <?endif;?>
        </div>
    </div>
    
    <?if(!$arResult["SHOW_DEFAULT"] && !$arResult["CROPPED"]):?>
        <form action="<?=$APPLICATION->GetCurPage(true);?>" method="POST" id="avatar-jcrop-coords">
            <input type="hidden" name="x" id="x-coords" value="0"/>
            <input type="hidden" name="y" id="y-coords" value="0"/>
            <input type="hidden" name="w" id="width" value="200"/>
            <input type="hidden" name="h" id="height" value="200"/>
            <input type="hidden" name="src" value="<?=$arResult["PREVIEW_PICTURE_SRC"]?>">
            <input type="hidden" name="action" value="crop">
            
            <button class="avatar-btn avatar-btn-submit" type="submit">
                <?=Loc::getMessage("CROP_BTN_TEXT")?>
            </button>
        </form>
    <?endif;?>
</div>


<script>
    let lastAction = false;
    let useAjax = false;
    let ajaxUrl = false;

    <?if(!empty($arResult["LAST_ACTION"])):?>
        lastAction = "<?=$arResult['LAST_ACTION']?>";
    <?endif;?>

    <?if($arResult["USE_AJAX"]):?>
        useAjax = true;
        ajaxUrl = "<?=$componentPath?>/ajax.php";
    <?endif;?>

    BX.ready(function() 
    {
        BX.messages = {
            "MAX_FILE_SIZE": '<?=Loc::getMessage("MAX_FILE_SIZE", ["#MAXFSIZE#"=>round((int)$arResult["MAX_FILE_SIZE"]/(1024*1024))]);?>',
            "INVALID_FILE_FORMAT": '<?=Loc::getMessage("INVALID_FILE_FORMAT")?>',
            "DOWNLOAD_BTN_TEXT": '<?=Loc::getMessage("DOWNLOAD_BTN_TEXT")?>',
            "CROP_WINDOW_TEXT": '<?=Loc::getMessage("CROP_WINDOW_TEXT",["#BTN_TEXT#"=>Loc::getMessage("CROP_BTN_TEXT")])?>',
            "CROP_BTN_TEXT": '<?=Loc::getMessage("CROP_BTN_TEXT")?>',
        }

        if (BX.type.isFunction(BX.EMC.Avatar.Create.init))
        {
            BX.EMC.Avatar.Create.init(lastAction, useAjax, ajaxUrl);
        }
    });
</script>

<div id="loader-screen">
    <div class="lds-default"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
</div>
