<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - CategoryOneConfig.php
 * 19.02.2023 19:04
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Vendor\Project\Dynamic\Internals\EditorConfig\BaseConfig;
use Vendor\Project\Dynamic\Service\Container;

/**
 * @class CategoryOneConfig
 * @package Vendor\Project\Dynamic\Internals\EditorConfig\Scheme
 */
class CategoryOneConfig extends BaseConfig
{
    /**
     * @return array
     */
    protected function getConfigScheme(): array
    {
        return [
            [
                'name' =>  "rq_sys_fields",
                'title' => "Required system fields",
                'type' => "section",
                'elements' => [
                    [
                        'name' => Item::FIELD_NAME_TITLE,
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name' =>  "ro_sys_fields",
                'title' => "Readonly system fields",
                'type' => "section",
                'elements' => [
                    [
                        'name' => Item::FIELD_NAME_XML_ID,
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name' =>  "assigned_sect",
                'title' => "Assigned section",
                'type' => "section",
                'elements' => [
                    [
                        'name' => Item::FIELD_NAME_ASSIGNED,
                        'optionFlags' => '1'
                    ],
                ]
            ],

            [
                'name' =>  "rq_uf_fields",
                'title' => "Required uf fields",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_EXAMPLE_STRING",
                        'optionFlags' => '1'
                    ],
                ]
            ],
            [
                'name' =>  "ro_uf_fields",
                'title' => "Readonly uf fields",
                'type' => "section",
                'elements' => [
                    [
                        'name' => "UF_CRM_". $this->typeId ."_EXAMPLE_LIST",
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => "UF_CRM_". $this->typeId ."_EXAMPLE_DATE",
                        'optionFlags' => '1'
                    ],
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function getRequiredFields(): array
    {
        return [
            Item::FIELD_NAME_TITLE,
            "UF_CRM_". $this->typeId ."_EXAMPLE_STRING"
        ];
    }

    /**
     * @return array
     */
    public function getReadonlyFields(): array
    {
        return [
            Item::FIELD_NAME_XML_ID,
            "UF_CRM_". $this->typeId ."_EXAMPLE_LIST",
            "UF_CRM_". $this->typeId ."_EXAMPLE_DATE",
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHiddenFields(): array
    {
        $fields = [];
        $item   = Container::getInstance()->getContext()->getItem();
        if ($item)
        {
            if ($item->isNew())
            {
                $fields[] = Item::FIELD_NAME_ASSIGNED;
            }
        }
        return array_merge(parent::getHiddenFields(), $fields);
    }
}