<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - DBTableInstaller.php
 * 30.11.2022 18:32
 * ==================================================
 */
namespace Vendor\Project\Basic\Internals\Installation;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;

/**
 * Class DBTableInstaller
 * @package Vendor\Project\Basic\Internals\Installation
 */
class DBTableInstaller
{
    private static array $dataClasses = [];

    /**
     * @throws \Exception
     */
    public static function install(): void
    {
        static::createDataTables(static::$dataClasses);
    }

    /**
     * @throws \Exception
     */
    public static function uninstall(): void
    {
        static::deleteDataTables(static::$dataClasses);
    }

    /**
     * @param array $dataClasses
     * @throws \Exception
     */
    private static function createDataTables(array $dataClasses): void
    {
        $connection = Application::getConnection();

        foreach ($dataClasses as $dataClass)
        {
            $dataTableName = Base::getInstance($dataClass)->getDBTableName();
            if(!$connection->isTableExists($dataTableName))
            {
                Base::getInstance($dataClass)->createDbTable();
            }
        }
    }

    /**
     * @param array $dataClasses
     * @throws \Exception
     */
    private static function deleteDataTables(array $dataClasses): void
    {
        $connection = Application::getConnection();

        foreach ($dataClasses as $dataClass)
        {
            $dataTableName = Base::getInstance($dataClass)->getDBTableName();
            if($connection->isTableExists($dataTableName))
            {
                $connection->dropTable($dataTableName);
            }
        }
    }
}