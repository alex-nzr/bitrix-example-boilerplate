<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Logger.php
 * 24.11.2022 14:46
 * ==================================================
 */

namespace Vendor\Project\Dynamic\Internals\Debug;

use Bitrix\Main\Diag\Debug;
use Vendor\Project\Dynamic\Config\Configuration;

/**
 * Class Logger
 * @package Vendor\Project\Dynamic\Internals\Debug
 */
class Logger extends Debug
{
    /**
     * @param ...$vars
     */
    public static function print(...$vars){
        foreach ($vars as $key => $var) {
            echo "$key---------------------------------------<pre>";
            print_r($var);
            echo "</pre>";
        }
    }

    /**
     * @param ...$vars
     */
    public static function printToFile(...$vars)
    {
        foreach ($vars as $key => $var) {
            static::writeToFile(
                $var,
                "$key---------------------------------------",
                Configuration::getInstance()->getLogFilePath()
            );
        }
    }

    /**
     * @param $var
     * @param string $varName
     * @param string $fileName
     */
    public static function writeToFile($var, $varName = "", $fileName = "")
    {
        parent::writeToFile($var, $varName, $fileName);
    }
}