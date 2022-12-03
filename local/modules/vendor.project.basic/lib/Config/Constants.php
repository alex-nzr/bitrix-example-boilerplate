<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Constants.php
 * 24.11.2022 12:34
 * ==================================================
 */

namespace Vendor\Project\Basic\Config;

/**
 * Class Constants
 * @package Vendor\Project\Basic\Config
 */
class Constants
{
    const OPTION_TYPE_FILE_POSTFIX     = '_FILE';
    const OPTION_KEY_SOME_TEXT_OPTION  = 'vendor_project_basic_some_text_option';
    const OPTION_KEY_SOME_FILE_OPTION  = 'vendor_project_basic_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;
    const OPTION_KEY_SOME_COLOR_OPTION = 'vendor_project_basic_some_color_option';
}