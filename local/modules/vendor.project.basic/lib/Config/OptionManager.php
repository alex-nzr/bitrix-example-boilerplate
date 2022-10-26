<?php
namespace Vendor\Project\Basic\Config;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use CAdminTabControl;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

/**
 * Class OptionManager
 * @package Vendor\Project\Basic\Config
 */
class OptionManager{

    private Request $request;
    private string  $moduleId;
    private array   $tabs;
    private string $formAction;
    public CAdminTabControl $tabControl;

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
                        'example_option',
                        Loc::getMessage($this->moduleId."_EXAMPLE_OPTION"),
                        "",
                        ['text', 5]
                    ],
                ]
            ],
            [
                'DIV'   => "access_tab",
                'TAB'   => Loc::getMessage($this->moduleId . "_TAB_RIGHTS"),
                'ICON'  => '',
                'TITLE' => Loc::getMessage($this->moduleId . "_TAB_TITLE_RIGHTS"),
            ]
        ];
    }

    /**
     * @return void
     */
    public function processRequest(): void
    {
        if ($this->request->isPost() && $this->request->getPost('Update') && check_bitrix_sessid())
        {
            foreach ($this->tabs as $arTab)
            {
                __AdmSettingsSaveOptions($this->moduleId, $arTab['OPTIONS']);
            }
        }
    }

    /**
     * @return void
     */
    public function startDrawHtml()
    {
        $this->tabControl->Begin();
        ?>
        <form method="POST" action="<?=$this->formAction?>" name="<?=$this->moduleId?>_settings">
        <?php
            foreach ($this->tabs as $arTab)
            {
                if(is_array($arTab['OPTIONS']))
                {
                    $this->tabControl->BeginNextTab();
                    __AdmSettingsDrawList($this->moduleId, $arTab['OPTIONS']);
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
}