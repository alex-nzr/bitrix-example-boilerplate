<?php

use Vendor\Project\Dynamic\Internals\Control\ServiceManager;

$moduleId = ServiceManager::getModuleId();
$MESS[$moduleId.'_MODULE_SETTINGS'] = "Настройки модуля";
$MESS[$moduleId.'_MAIN_SETTINGS']   = "Основные настройки";

$MESS[$moduleId.'_TAB_RIGHTS']       = "Доступ";
$MESS[$moduleId.'_TAB_TITLE_RIGHTS'] = "Уровень доступа к модулю";