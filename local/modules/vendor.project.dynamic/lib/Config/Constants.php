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

namespace Vendor\Project\Dynamic\Config;

/**
 * Class Constants
 * @package Vendor\Project\Dynamic\Config
 */
class Constants
{
    const DYNAMIC_TYPE_CODE                 = 'DYNAMIC_ENTITY_EXAMPLE';
    const DYNAMIC_TYPE_TITLE                = 'Example smart';
    const DYNAMIC_TYPE_CUSTOM_SECTION_CODE  = 'example';
    const DYNAMIC_TYPE_CUSTOM_SECTION_TITLE = 'Example section';

    const CUSTOM_PAGE_LIST    = 'list';
    const CUSTOM_PAGE_EXAMPLE = 'somePage';

    const OPTION_TYPE_FILE_POSTFIX     = '_FILE';
    const OPTION_KEY_DYNAMIC_TYPE_ID   = 'vendor_project_dynamic_type_id';
    const OPTION_KEY_SOME_TEXT_OPTION  = 'vendor_project_dynamic_some_text_option';
    const OPTION_KEY_SOME_FILE_OPTION  = 'vendor_project_dynamic_some_file_option'.self::OPTION_TYPE_FILE_POSTFIX;
    const OPTION_KEY_SOME_COLOR_OPTION = 'vendor_project_dynamic_some_color_option';
}