<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - Factory.php
 * 24.11.2022 14:46
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service;

use Bitrix\Crm\Item;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Factory\Dynamic;
use Bitrix\Crm\Service\Operation;
use CCrmFieldInfoAttr;

/**
 * Class Factory
 * @package Vendor\Project\Dynamic\Service
 */
class Factory extends Dynamic
{
    /**
     * @param \Bitrix\Crm\Model\Dynamic\Type $type
     */
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Add
     */
    public function getAddOperation(Item $item, Context $context = null): Operation\Add
    {
        return parent::getAddOperation($item, $context);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Update
     */
    public function getUpdateOperation(Item $item, Context $context = null): Operation\Update
    {
        return parent::getUpdateOperation($item, $context);
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Delete
     */
    public function getDeleteOperation(Item $item, Context $context = null): Operation\Delete
    {
        return parent::getDeleteOperation($item, $context);
    }

    /**
     * @return \Bitrix\Crm\Service\EditorAdapter
     * @throws \Exception
     */
    public function getEditorAdapter(): \Bitrix\Crm\Service\EditorAdapter
    {
        if (!$this->editorAdapter)
        {
            $this->editorAdapter = new EditorAdapter($this->getFieldsCollection(), $this->getDependantFieldsMap());
            if ($this->isClientEnabled())
            {
                $this->editorAdapter->addEntityField(
                    EditorAdapter::getClientField(
                        $this->getFieldCaption(EditorAdapter::FIELD_CLIENT),
                        EditorAdapter::FIELD_CLIENT,
                        EditorAdapter::FIELD_CLIENT_DATA_NAME,
                        ['entityTypeId' => $this->getEntityTypeId()]
                    )
                );
            }
            if ($this->isLinkWithProductsEnabled())
            {
                $this->editorAdapter->addEntityField(
                    EditorAdapter::getOpportunityField(
                        $this->getFieldCaption(EditorAdapter::FIELD_OPPORTUNITY),
                        EditorAdapter::FIELD_OPPORTUNITY,
                        $this->isPaymentsEnabled()
                    )
                );
                $this->editorAdapter->addEntityField(
                    EditorAdapter::getProductRowSummaryField(
                        $this->getFieldCaption(EditorAdapter::FIELD_PRODUCT_ROW_SUMMARY)
                    )
                );
            }
        }

        return parent::getEditorAdapter();
    }
}