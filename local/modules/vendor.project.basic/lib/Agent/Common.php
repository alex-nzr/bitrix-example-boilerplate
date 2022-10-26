<?php
namespace Vendor\Project\Basic\Agent;

use Bitrix\Main\Loader;
use Throwable;
use Vendor\Project\Basic\Config\Constants;
use Vendor\Project\Basic\Internals\Debug\Logger;

/**
 * Class Common
 * @package Vendor\Project\Basic\Agent
 */
class Common
{
    protected static string $logFile = Constants::LOG_FILENAME;

    public static function exampleAgentFunction(): string
    {
        try
        {
            Loader::includeModule('iblock');
        }
        catch (Throwable $e)
        {
            Logger::writeToFile(
                date("d.m.Y H:i:s") . " | " . $e->getMessage(),
                'Error in agent ' . __METHOD__,
                static::$logFile
            );
        }

        return __METHOD__."();";
    }
}