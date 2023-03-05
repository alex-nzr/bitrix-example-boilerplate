<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - OptionManager.php
 * 21.11.2022 11:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use CAdminTabControl;
use CFile;
use Exception;
use Vendor\Project\Dynamic\Internals\Control\ServiceManager;
use function htmlSpecialCharsBx;
use function ShowError;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

/**
 * Class OptionManager
 * @package Vendor\Project\Dynamic\Config
 */
class OptionManager{

    private Request $request;
    private string  $moduleId;
    private array   $tabs;
    private string $formAction;
    public CAdminTabControl $tabControl;

    /**
     * OptionManager constructor.
     * @param string $moduleId
     */
    public function __construct(string $moduleId)
    {
        $this->request  = Context::getCurrent()->getRequest();
        $this->moduleId = $moduleId;
        $this->setTabs();
        $this->tabControl = new CAdminTabControl('tabControl', $this->tabs);
        $this->formAction = $this->request->getRequestedPage() . "?" . http_build_query([
                'mid'  => htmlspecialcharsbx($this->request->get('mid')),
                'lang' => $this->request->get('lang')
            ]);
    }

    /**
     * @return void
     */
    protected function setTabs()
    {
        $this->tabs = [
            [
                'DIV'   => "settings_tab",
                'TAB'   => Loc::getMessage($this->moduleId."_MODULE_SETTINGS"),
                'ICON'  => '',
                'TITLE' => Loc::getMessage($this->moduleId."_MODULE_SETTINGS"),
                "OPTIONS" => [
                    Loc::getMessage($this->moduleId."_MAIN_SETTINGS"),
                    [
                        Constants::OPTION_KEY_SOME_TEXT_OPTION,
                        'Some text option',
                        "placeholder value",
                        ['text', 50]
                    ],
                    [
                        Constants::OPTION_KEY_SOME_FILE_OPTION,
                        'Some file option',
                        "",
                        ['file']
                    ],
                    [
                        Constants::OPTION_KEY_SOME_COLOR_OPTION,
                        'Some color-picker option',
                        "#025ea1",
                        ['colorPicker']
                    ],
                    [ 'note' => 'Some note in option page'],
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function processRequest(): void
    {
        try {
            if ($this->request->isPost() && $this->request->getPost('Update') && check_bitrix_sessid())
            {
                foreach ($this->tabs as $arTab)
                {
                    foreach ($arTab['OPTIONS'] as $arOption)
                    {
                        if(!is_array($arOption) || !empty($arOption['note']))
                        {
                            continue;
                        }
                        $optionName = $arOption[0];
                        $optionValue = $this->request->getPost($optionName);

                        $fileOptionPostfix = Constants::OPTION_TYPE_FILE_POSTFIX;
                        if (substr($optionName, -strlen($fileOptionPostfix)) === $fileOptionPostfix)
                        {
                            $moduleId = ServiceManager::getModuleId();
                            $currentValue = Option::get($moduleId, $optionName);
                            $optionValue = $this->request->getFile($optionName);

                            if (empty($optionValue['name']) && !empty($currentValue)){
                                continue;
                            }

                            $arFile = $optionValue;
                            $arFile["MODULE_ID"] = $moduleId;

                            if (strlen($arFile["name"]) > 0)
                            {
                                $fid = CFile::SaveFile($arFile, $arFile["MODULE_ID"]);
                                $optionValue = (int)$fid > 0 ? $fid : '';
                            }
                        }
                        Option::set(
                            $this->moduleId,
                            $optionName,
                            is_array($optionValue) ? json_encode($optionValue) : $optionValue
                        );
                    }
                }
            }
        }
        catch (Exception $e){
            ShowError($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function startDrawHtml()
    {
        $this->tabControl->Begin();
        ?>
        <form method="POST" action="<?=$this->formAction?>" name="<?=$this->moduleId?>_settings" enctype="multipart/form-data">
        <?php
        foreach ($this->tabs as $arTab)
        {
            if(is_array($arTab['OPTIONS']))
            {
                $this->tabControl->BeginNextTab();
                $this->drawSettingsList($this->moduleId, $arTab['OPTIONS']);
            }
        }
    }

    /**
     * @return void
     */
    public function endDrawHtml()
    {
        $this->tabControl->Buttons();?>
        <?=bitrix_sessid_post();?>
        <input type="submit" name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>" class="adm-btn-save">
        <input type="reset"  name="reset" value="<?=Loc::getMessage('MAIN_RESET')?>">
        </form>
        <?php
        $this->tabControl->End();
    }

    /**
     * @param string $module_id
     * @param $option
     */
    protected function drawSettingsRow(string $module_id, $option)
    {
        if(empty($option))return;

        if(!is_array($option))
        {
            echo "<tr class='heading'><td colspan='2'>$option</td></tr>";
        }
        elseif(isset($option["note"]))
        {
            echo    "<tr>
                        <td colspan='2'>
                            <div class='adm-info-message-wrap'>
                                <div class='adm-info-message'>{$option["note"]}</div>
                            </div>
                        </td>
                    </tr>";
        }
        else
        {
            $currentVal = Option::get($module_id, $option[0], $option[2]);
            echo "<tr>";
            $this->renderTitle($option[1]);
            $this->renderInput($option, $currentVal ?? '');
            echo "</tr>";
        }
    }

    /**
     * @param string $module_id
     * @param array $arParams
     */
    protected function drawSettingsList(string $module_id, array $arParams)
    {
        foreach($arParams as $Option)
        {
            $this->drawSettingsRow($module_id, $Option);
        }
    }

    /**
     * @param string $text
     */
    protected function renderTitle(string $text)
    {
        echo "<td><span>$text</span></td>";
    }

    /**
     * @param array $option
     * @param string $val
     */
    protected function renderInput(array $option, string $val)
    {
        $name  = $option[0];
        $type  = $option[3];
        ?>
        <td style="width: 50%">
        <label for="<?=$name?>" class="module-option-label">
            <?
            switch ($type[0])
            {
                case "checkbox":
                    $checked = ($val === "Y") ? "checked" : '';
                    echo "<input type='checkbox' id='$name' name='$name' value='Y' $checked>";
                    break;
                case "text":
                case "password":
                    $autocomplete = $type[0] === 'password' ? 'autocomplete="new-password"' : '';
                    echo "<input type='$type[0]' id='$name' name='$name' value='$val' size='$type[1]' maxlength='255' $autocomplete>";
                    break;
                case "number":
                    echo "<input type='number' id='$name' name='$name' value='$val' size='$type[1]' min='-999999999' max='999999999'>";
                    break;
                case "select":
                    $arr = is_array($type[1]) ? $type[1] : [];
                    echo "<select name='$name'>";
                    foreach($arr as $optionVal => $displayVal)
                    {
                        $selected = ($val === $optionVal) ? "selected" : '';
                        echo "<option value='$optionVal' $selected>$displayVal</option>";
                    }
                    echo "</select>";
                    break;
                case "multiselect":
                    $arr = is_array($type[1]) ? $type[1] : [];
                    $name .= '[]';
                    $arr_val = json_decode($val);
                    echo "<select name='$name' size='5' multiple>";
                    foreach($arr as $optionVal => $displayVal)
                    {
                        $selected = (in_array($optionVal, $arr_val)) ? "selected" : '';
                        echo "<option value='$optionVal' $selected>$displayVal</option>";
                    }
                    echo "</select>";
                    break;
                case "textarea":
                    echo "<textarea rows='$type[1]' cols='$type[2]' name='$name'>$val</textarea>";
                    break;
                case "staticText":
                    echo "<span>$val</span>";
                    break;
                case "colorPicker":
                    echo "<input type='text' id='$name' name='$name' value='$val' readonly/>
                              <script>
                                BX.ready(function() {
                                    if (BX.AdminSection?.OptionPage){
                                        BX.AdminSection.OptionPage.bindColorPickerToNode('$name', '$name', '$option[2]');
                                    }
                                });
                              </script>";
                    break;
                case "file":
                    if (is_numeric($val) && (int)$val > 0)
                    {
                        $arFile = CFile::GetFileArray($val);
                        if (!empty($arFile))
                        {
                            $fileLink = $arFile['SRC'];
                            $fileName = $arFile['FILE_NAME'];
                            if (CFile::IsImage($fileName))
                            {
                                echo "<div>
                                        <a href='$fileLink' download='$fileName'><img src='$fileLink' alt='image' width='200'></a>
                                      </div>";
                            }
                            else
                            {
                                echo "<div>
                                        <a href='$fileLink' download='$fileName'>$fileName</a>
                                      </div>";
                            }
                        }
                    }
                    echo "<input type='file' id='$name' name='$name'/>";
                    break;
            }
            ?>
        </label>
        </td><?
    }
}