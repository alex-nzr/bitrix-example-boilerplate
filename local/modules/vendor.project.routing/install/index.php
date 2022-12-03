<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class vendor_project_routing extends CModule
{
    private CMain $App;

    public function __construct()
    {
        $this->App = $GLOBALS['APPLICATION'];

        $arModuleVersion = [];
        include(__DIR__."/version.php");

        $this->MODULE_ID = GetModuleID(__FILE__);

        $this->MODULE_VERSION       = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE  = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME          = Loc::getMessage($this->MODULE_ID . "_MODULE_NAME");
        $this->MODULE_DESCRIPTION   = Loc::getMessage($this->MODULE_ID . "_MODULE_DESCRIPTION");
        $this->PARTNER_NAME         = Loc::getMessage($this->MODULE_ID . "_PARTNER_NAME");
        $this->PARTNER_URI          = Loc::getMessage($this->MODULE_ID . "_PARTNER_URI");
        $this->MODULE_SORT          = 10;
        $this->MODULE_GROUP_RIGHTS  = "Y";
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
    }

    public function DoInstall(): bool
    {
        try
        {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();

            return true;
        }
        catch (Throwable $e)
        {
            $this->App->ThrowException(Loc::getMessage($this->MODULE_ID . "_INSTALL_ERROR")." - ". $e->getMessage());
            $this->DoUninstall();
            return false;
        }
    }

    public function DoUninstall(): bool
    {
        try
        {
            $this->UnInstallDB();
            ModuleManager::unRegisterModule($this->MODULE_ID);
            return true;
        }
        catch(Throwable $e)
        {
            $this->App->ThrowException(Loc::getMessage($this->MODULE_ID . "_UNINSTALL_ERROR")." - ". $e->getMessage());
            return false;
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function InstallDB()
    {

    }

    /**
     * @throws \Exception
     */
    public function UnInstallDB()
    {
        Option::delete($this->MODULE_ID);
    }

    public function InstallEvents()
    {

    }

    public function UnInstallEvents()
    {

    }

    public function InstallFiles()
    {

    }

    public function UnInstallFiles()
    {

    }

    public function GetModuleRightList(): array
    {
        return [
            "reference_id" => ["D","R","W"],
            "reference" => [
                "[D] ".Loc::getMessage($this->MODULE_ID . "_DENIED"),
                "[R] ".Loc::getMessage($this->MODULE_ID . "_READING"),
                "[W] ".Loc::getMessage($this->MODULE_ID . "_FULL")
            ]
        ];
    }
}