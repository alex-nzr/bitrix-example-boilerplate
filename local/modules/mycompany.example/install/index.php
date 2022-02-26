<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class mycompany_example extends CModule
{
    private CMain $App;
    public function __construct(){
        $this->App = $GLOBALS['APPLICATION'];

        $arModuleVersion = [];
        include(__DIR__."/version.php");

        $this->MODULE_ID            = str_replace("_", ".", get_class($this));
        $this->MODULE_VERSION       = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE  = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME   = Loc::getMessage("MYCOMPANY_EXAMPLE_MODULE_NAME");
        $this->MODULE_DESCRIPTION   = Loc::getMessage("MYCOMPANY_EXAMPLE_MODULE_DESCRIPTION");
        $this->PARTNER_NAME         = Loc::getMessage("MYCOMPANY_EXAMPLE_PARTNER_NAME");
        $this->PARTNER_URI          = Loc::getMessage("MYCOMPANY_EXAMPLE_PARTNER_URI");
        $this->MODULE_SORT          = 1;
        $this->MODULE_GROUP_RIGHTS  = "Y";
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
    }

    public function DoInstall(): bool
    {
        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){

            $this->InstallFiles();
            $db = $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();
        }else{

            $this->App->ThrowException(
                Loc::getMessage("MYCOMPANY_EXAMPLE_INSTALL_ERROR")
            );
        }

        $this->App->IncludeAdminFile(
            Loc::getMessage("MYCOMPANY_EXAMPLE_INSTALL_TITLE"),
            __DIR__."/step.php"
        );

        return false;
    }

    public function DoUninstall(): bool
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->App->IncludeAdminFile(
            Loc::getMessage("MYCOMPANY_EXAMPLE_UNINSTALL_TITLE"),
            __DIR__."/unstep.php"
        );

        return false;
    }

    function InstallDB(): bool
    {
        return false;
    }

    function UnInstallDB(): bool
    {
        try {
            Option::delete($this->MODULE_ID);
        }catch(Exception $e){
            $this->App->ThrowException(Loc::getMessage("MYCOMPANY_EXAMPLE_UNINSTALL_ERROR"));
        }
        return false;
    }

    function InstallEvents()
    {
    }

    function UnInstallEvents()
    {
    }

    function InstallFiles()
    {
    }

    function UnInstallFiles()
    {
    }
}