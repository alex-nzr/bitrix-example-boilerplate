<?php
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\IO\Directory as Dir;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Vendor\Project\Basic\Agent\AgentManager;
use Vendor\Project\Basic\Internals\Control\EventManager as CustomEventManager;

Loc::loadMessages(__FILE__);

class vendor_project_basic extends CModule
{
    private CMain $App;
    private ?string $docRoot;
    private string $partnerId;
    private string $projectId;
    private string $moduleIdShort;

    public function __construct()
    {
        $this->App = $GLOBALS['APPLICATION'];
        $this->docRoot = Application::getDocumentRoot();

        $arModuleVersion = [];
        include(__DIR__."/version.php");

        $this->MODULE_ID = GetModuleID(__FILE__);

        $this->partnerId     = explode('.', $this->MODULE_ID)[0];
        $this->projectId     = explode('.', $this->MODULE_ID)[1];
        $this->moduleIdShort = explode('.', $this->MODULE_ID)[2];

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
            $this->checkRequirements();

            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            $this->InstallEvents();
            $this->InstallDB();
            $this->InstallFiles();
            $this->InstallAgents();

            $this->App->IncludeAdminFile(
                Loc::getMessage($this->MODULE_ID . "_INSTALL_TITLE"),
                __DIR__."/step.php"
            );
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
            Loader::includeModule($this->MODULE_ID);
            $request = Context::getCurrent()->getRequest();

            if ((int)$request->get('step') < 2)
            {
                $this->App->IncludeAdminFile(
                    Loc::getMessage($this->MODULE_ID . "_UNINSTALL_TITLE"),
                    __DIR__."/unStep_1.php"
                );
            }
            else
            {
                $this->UnInstallFiles();
                $this->UnInstallEvents();
                $this->UnInstallAgents();
                if ($request->get('saveData') !== "Y"){
                    $this->UnInstallDB();
                }

                ModuleManager::unRegisterModule($this->MODULE_ID);

                $this->App->IncludeAdminFile(
                    Loc::getMessage($this->MODULE_ID . "_UNINSTALL_TITLE"),
                    __DIR__."/unStep_2.php"
                );
            }
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
        CustomEventManager::addBasicEventHandlers();
    }

    public function UnInstallEvents()
    {
        CustomEventManager::removeBasicEventHandlers();
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__.'/js/', $this->docRoot.'/local/js/'.$this->partnerId."/".$this->projectId."/".$this->moduleIdShort, true, true);
        CopyDirFiles(__DIR__.'/css/', $this->docRoot.'/local/css/'.$this->partnerId."/".$this->projectId."/".$this->moduleIdShort, true, true);
        CopyDirFiles(__DIR__.'/admin/', $this->docRoot.'/bitrix/admin', true);
        CopyDirFiles(__DIR__.'/components/', $this->docRoot.'/local/components', true, true);
        CopyDirFiles(__DIR__.'/public/', $this->docRoot, true, true);
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__.'/admin/', $this->docRoot.'/bitrix/admin');
        if (Dir::isDirectoryExists($this->docRoot . '/local/css/'.$this->partnerId . "/".$this->projectId.'/'.$this->moduleIdShort.'/')){
            Dir::deleteDirectory($this->docRoot . '/local/css/'.$this->partnerId . "/".$this->projectId.'/'.$this->moduleIdShort.'/');
        }
        if (Dir::isDirectoryExists($this->docRoot . '/local/js/'.$this->partnerId . "/".$this->projectId.'/'.$this->moduleIdShort.'/')){
            Dir::deleteDirectory($this->docRoot . '/local/js/'.$this->partnerId . "/".$this->projectId.'/'.$this->moduleIdShort.'/');
        }

        if (Dir::isDirectoryExists($path = $this->docRoot.'/local/components/'.$this->partnerId."/")) {
            if ($dir = opendir($path)) {
                while ($item = readdir($dir))
                {
                    if (strpos($item, $this->projectId.".".$this->moduleIdShort) !== false)
                    {
                        if (is_dir($path . $item))
                        {
                            Dir::deleteDirectory($path . $item);
                        }
                    }
                }
                closedir($dir);
            }
        }
    }

    /**
     * @return bool
     */
    public function InstallAgents(): bool
    {

        return AgentManager::getInstance()->addAgents();
    }

    function UnInstallAgents(): bool
    {

        return AgentManager::getInstance()->removeAgents();
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

    /**
     * @throws \Exception
     */
    protected function checkRequirements(): void
    {
        $requirePhp = '7.4.0';

        if (!CheckVersion(PHP_VERSION, $requirePhp))
        {
            throw new Exception(Loc::getMessage(
                $this->MODULE_ID . '_INSTALL_REQUIRE_PHP',
                [ '#VERSION#' => $requirePhp ]
            ));
        }

        $requireModules = [
            'main'  => '22.0.0',
        ];

        foreach ($requireModules as $moduleName => $moduleVersion)
        {
            $currentVersion = ModuleManager::getVersion($moduleName);

            if (!CheckVersion($currentVersion, $moduleVersion))
            {
                throw new Exception(Loc::getMessage($this->MODULE_ID . '_INSTALL_ERROR_VERSION', [
                    '#MODULE#' => $moduleName,
                    '#VERSION#' => $moduleVersion
                ]));
            }
        }
    }
}