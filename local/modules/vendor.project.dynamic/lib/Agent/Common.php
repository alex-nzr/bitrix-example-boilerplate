<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Common.php
 * 24.11.2022 12:30
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Agent;


use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Internals\Debug\Logger;
use Throwable;

/**
 * Class Common
 * @package Vendor\Project\Dynamic\Agent
 */
class Common
{
    /**
     * @return string
     */
    public static function someFunc(): string
    {
        try
        {
            //Agent logic
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @param \Throwable $e
     * @param string $method
     */
    private static function logError(Throwable $e, string $method)
    {
        $code = !empty($e->getCode()) ? $e->getCode() : 0;
        Logger::writeToFile(
            "Code: $code. Description: " . $e->getMessage(),
            date("d.m.Y H:i:s") . ' ' . $method,
            Configuration::getInstance()->getLogFilePath()
        );
    }
}