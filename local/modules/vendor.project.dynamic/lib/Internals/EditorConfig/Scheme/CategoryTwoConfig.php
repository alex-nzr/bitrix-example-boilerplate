<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - CategoryTwoConfig.php
 * 19.02.2023 19:08
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\EditorConfig\Scheme;

use Bitrix\Crm\Item;
use Vendor\Project\Dynamic\Config\Configuration;
use Vendor\Project\Dynamic\Config\Constants;
use Vendor\Project\Dynamic\Internals\EditorConfig\BaseConfig;
use Vendor\Project\Dynamic\Item\Dynamic;
use Vendor\Project\Dynamic\Service\Container;

/**
 * @class CategoryTwoConfig
 * @package Vendor\Project\Dynamic\Internals\EditorConfig\Scheme
 */
class CategoryTwoConfig extends BaseConfig
{
    /**
     * @return array[]
     * @throws \Exception
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
                        'name' => Item::FIELD_NAME_XML_ID,
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
                        'name' => Item::FIELD_NAME_TITLE,
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
                        'name' => $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_LIST),
                        'optionFlags' => '1'
                    ],
                    [
                        'name' => $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_DATE),
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
                        'name' => $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_STRING),
                        'optionFlags' => '1'
                    ],
                ]
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRequiredFields(): array
    {
        $requiredFieldConfigForNewItem = $this->getRequiredFieldConfigForStage(
            Configuration::getInstance()->getDefaultCategoryId(),
            Constants::DYNAMIC_STAGE_DEFAULT_NEW
        );
        return [
            Item::FIELD_NAME_XML_ID => $this->getConfigForAlwaysRequiredField(),
            $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_LIST) => $requiredFieldConfigForNewItem,
            $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_DATE) => $requiredFieldConfigForNewItem,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReadonlyFields(): array
    {
        $fields = [
            Item::FIELD_NAME_TITLE,
        ];

        $item = Container::getInstance()->getContext()->getItem();
        if ($item)
        {
            if ($item->isNew())
            {
                $fields[] = $this->ufBroker->getUfNameByCode(Dynamic::UF_CODE_EXAMPLE_STRING);
            }
            else
            {
                $fields[] = Item::FIELD_NAME_XML_ID;
            }
        }

        return $fields;
    }

    public function getHiddenFields(): array
    {
        return array_merge(parent::getHiddenFields(), [
            Item::FIELD_NAME_ASSIGNED
        ]);
    }
}