<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - ConfigType.php
 * 19.02.2023 18:55
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\EditorConfig;

use Vendor\Project\Dynamic\Config\Constants;

/**
 * @class ConfigType
 * @package Vendor\Project\Dynamic\Internals\EditorConfig
 */
class ConfigType
{
    const CATEGORY_1 = Constants::DYNAMIC_CATEGORY_DEFAULT_CODE;
    const CATEGORY_2 = Constants::DYNAMIC_CATEGORY_CUSTOM_CODE;

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        return [
            static::CATEGORY_1,
            static::CATEGORY_2
        ];
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isTypeSupported(string $type): bool
    {
        return in_array($type, static::getSupportedTypes());
    }
}