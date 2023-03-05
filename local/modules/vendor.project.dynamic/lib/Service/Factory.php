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

use Bitrix\Crm\Item as BaseItem;
use Bitrix\Crm\Model\Dynamic\Type;
use Bitrix\Crm\Service\Context;
use Bitrix\Crm\Service\Factory\Dynamic as DynamicFactory;
use Bitrix\Crm\Service\Operation;
use Bitrix\Main\Result;
use Vendor\Project\Dynamic\Item;
use Vendor\Project\Dynamic\Service\Operation\Action\BeforeAdd;
use Vendor\Project\Dynamic\Service\Operation\Action\BeforeDelete;
use Vendor\Project\Dynamic\Service\Operation\Action\BeforeUpdate;

/**
 * Class Factory
 * @package Vendor\Project\Dynamic\Service
 */
class Factory extends DynamicFactory
{
    protected $itemClassName = Item\Dynamic::class;

    /**
     * @param \Bitrix\Crm\Model\Dynamic\Type $type
     */
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

    /**
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function add(array $fields): Result
    {
        $item = $this->createItem();

        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }

        $saveOperation = $this->getAddOperation($item);
        $res = $saveOperation->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param array $fields
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function update(BaseItem $item, array $fields): Result
    {
        foreach ($fields as $field => $value) {
            $item->set($field, $value);
        }
        $updateOperation = $this->getUpdateOperation($item);
        $res = $updateOperation->launch();
        if ($res->isSuccess())
        {
            $res->setData(['ID' => $item->getId()]);
        }
        return $res;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function delete(BaseItem $item): Result
    {
        $deleteOperation = $this->getDeleteOperation($item);
        return $deleteOperation->launch();
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Add
     * @throws \Exception
     */
    public function getAddOperation(BaseItem $item, Context $context = null): Operation\Add
    {
        return parent::getAddOperation($item, $context)
            ->addAction(Operation::ACTION_BEFORE_SAVE, new BeforeAdd());
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Update
     * @throws \Exception
     */
    public function getUpdateOperation(BaseItem $item, Context $context = null): Operation\Update
    {
        return parent::getUpdateOperation($item, $context)
            ->addAction(Operation::ACTION_BEFORE_SAVE, new BeforeUpdate());
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @param \Bitrix\Crm\Service\Context|null $context
     * @return \Bitrix\Crm\Service\Operation\Delete
     * @throws \Exception
     */
    public function getDeleteOperation(BaseItem $item, Context $context = null): Operation\Delete
    {
        return parent::getDeleteOperation($item, $context)
            ->addAction(Operation::ACTION_BEFORE_SAVE, new BeforeDelete());
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

            $this->editorAdapter
                ->setTypeId($this->getType()->getId())
                ->setEntityTypeId($this->getEntityTypeId())
                ->setCrmContext(Container::getInstance()->getContext());

            if ($this->isClientEnabled())
            {
                $this->editorAdapter->addClientField($this->getFieldCaption(EditorAdapter::FIELD_CLIENT));
            }
            if ($this->isLinkWithProductsEnabled())
            {
                $this->editorAdapter->addOpportunityField(
                    $this->getFieldCaption(EditorAdapter::FIELD_OPPORTUNITY),
                    $this->isPaymentsEnabled()
                );
                $this->editorAdapter->addProductRowSummaryField(
                    $this->getFieldCaption(EditorAdapter::FIELD_PRODUCT_ROW_SUMMARY)
                );
            }
        }

        return $this->editorAdapter;
    }
}