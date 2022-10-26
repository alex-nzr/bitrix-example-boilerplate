<?php
namespace Vendor\Project\Basic\Internals\Debug;

use Bitrix\Main\Diag\Debug;
use Vendor\Project\Basic\Config\Constants;

/**
 * Class Logger
 * @package Vendor\Project\Basic\Internals\Debug
 */
class Logger extends Debug
{
    /**
     * @param ...$vars
     */
    public static function print(...$vars){
        foreach ($vars as $key => $var) {
            echo "№$key.<pre>";print_r($var);echo "</pre>";
        }
    }

    /**
     * @param ...$vars
     */
    public static function printToFile(...$vars)
    {
        foreach ($vars as $key => $var) {
            static::writeToFile($var, "№".$key.'.', Constants::LOG_FILENAME);
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