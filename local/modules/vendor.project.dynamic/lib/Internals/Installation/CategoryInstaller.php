<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - CategoryInstaller.php
 * 06.12.2022 20:23
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\Installation;

use Bitrix\Crm\Category\Entity\ItemCategory;
use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\StatusTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Vendor\Project\Dynamic\Config\Constants;
use CCrmStatus;
use Vendor\Project\Dynamic\Service\Integration\Crm\Automation\TunnelManager;

/**
 * Class CategoryInstaller
 * @package Vendor\Project\Dynamic\Internals\Installation
 */
class CategoryInstaller
{
    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(int $entityTypeId): Result
    {
        $result = new Result;
        $tunnels = [];
        foreach (static::getCategoriesData() as $category)
        {
            if ($category['IS_DEFAULT'] !== 'Y')
            {
                $checkResult = static::createCategoryIfNotExists($entityTypeId, $category['TITLE'], $category['CODE']);
                if (!$checkResult->isSuccess())
                {
                    $result->addErrors($checkResult->getErrors());
                }

                $categoryId = (int)$checkResult->getData()['ID'];
            }
            else
            {
                $categoryId = static::getDefaultCategoryId($entityTypeId);
            }

            if ($categoryId <= 0)
            {
                $result->addError(new Error('Category ID cannot be less than zero. Error in category - '.$category['CODE']));
            }

            $categoryResult = static::setupCategoryData(
                $entityTypeId, $categoryId, $category['TITLE'], $category['CODE'], $category['STATUSES']
            );
            if(!$categoryResult->isSuccess())
            {
                $result->addErrors($categoryResult->getErrors());
            }

            if (!empty($category['TUNNELS']))
            {
                foreach ($category['TUNNELS'] as $tunnel)
                {
                    $tunnel['SRC_CATEGORY_ID'] = $categoryId;
                    $tunnels[] = $tunnel;
                }
            }
        }

        $tunnelsResult = static::setupTunnels($entityTypeId, $tunnels);
        if(!$tunnelsResult->isSuccess())
        {
            $result->addErrors($tunnelsResult->getErrors());
        }

        return $result;
    }

    /**
     * @param int $entityTypeId
     * @param int $categoryId
     * @param string $title
     * @param string $code
     * @param array $statuses
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    private static function setupCategoryData(
        int $entityTypeId, int $categoryId, string $title, string $code, array $statuses): Result
    {
        $result = new Result();

        $categoryObj = ItemCategoryTable::getByPrimary($categoryId)->fetchObject();
        if (!empty($categoryObj->getId()))
        {
            $categoryObj->setName($title);
            $categoryObj->set('CODE', $code);
            $changeRes = $categoryObj->save();
            if ($changeRes->isSuccess())
            {
                $currentStatuses = StatusTable::query()
                    ->setSelect(['*'])
                    ->setFilter(['=CATEGORY_ID' => $categoryId])
                    ->fetchCollection()
                    ->getAll();

                $updRes = static::setCategoryStatuses($currentStatuses, $statuses, $entityTypeId, $categoryId);
                if (!$updRes->isSuccess())
                {
                    $result->addErrors($updRes->getErrors());
                }
            }
            else
            {
                $result->addErrors($changeRes->getErrors());
            }
        }
        else
        {
            $result->addError(new Error("Category with ID $categoryId not found in ItemCategoryTable"));
        }

        return $result;
    }

    /**
     * @param array $currentStatuses
     * @param array $newStatuses
     * @param int $entityTypeId
     * @param int $categoryId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    private static function setCategoryStatuses(
        array $currentStatuses, array $newStatuses, int $entityTypeId, int $categoryId): Result
    {
        $result = new Result();

        foreach ($currentStatuses as $status)
        {
            $statusCode = substr($status->getStatusId(), strlen(static::getStatusPrefix($entityTypeId, $categoryId)));
            if (array_key_exists($statusCode, $newStatuses))
            {
                $status->setName($newStatuses[$statusCode]["NAME"]);
                $status->setSort($newStatuses[$statusCode]["SORT"]);
                $status->setColor($newStatuses[$statusCode]["COLOR"]);
                $status->setSemantics($newStatuses[$statusCode]["SEMANTICS"]);
                $res = $status->save();
                unset($newStatuses[$statusCode]);
            }
            else
            {
                if (!$status->getSystem())
                {
                    $res = $status->delete();
                }
            }

            if (!empty($res) && !$res->isSuccess()){
                $result->addErrors($res->getErrors());
            }
        }

        foreach ($newStatuses as $statusId => $statusData)
        {
            $fullStatusId = static::getStatusPrefix($entityTypeId, $categoryId) . $statusId;
            $entityId     = "DYNAMIC_".$entityTypeId."_STAGE_".$categoryId;
            $statusObj = StatusTable::createObject();
            $statusObj->setEntityId($entityId);
            $statusObj->setStatusId($fullStatusId);
            $statusObj->setCategoryId($categoryId);
            $statusObj->setName($statusData["NAME"]);
            $statusObj->setSort($statusData["SORT"]);
            $statusObj->setColor($statusData["COLOR"]);
            $statusObj->setSemantics($statusData["SEMANTICS"]);
            $res = $statusObj->save();
            if (!$res->isSuccess()){
                $result->addErrors($res->getErrors());
            }
        }

        return $result;
    }

    /**
     * @param int $entityTypeId
     * @param int $categoryId
     * @return string
     */
    private static function getStatusPrefix(int $entityTypeId, int $categoryId): string
    {
        return CCrmStatus::getDynamicEntityStatusPrefix($entityTypeId, $categoryId) . ":";
    }

    /**
     * @param int $entityTypeId
     * @param string $title
     * @param string $code
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    private static function createCategoryIfNotExists(int $entityTypeId, string $title, string $code): Result
    {
        $result     = new Result;
        $categoryId = null;

        $category   = ItemCategoryTable::query()
            ->setSelect(['ID'])
            ->setFilter(['=ENTITY_TYPE_ID' => $entityTypeId, 'CODE' => $code])
            ->fetch();
        if (!empty($category))
        {
            $categoryId = (int)$category['ID'];
        }
        else
        {
            $object = ItemCategoryTable::createObject();
            $object->setEntityTypeId($entityTypeId);
            $object->setName($title);
            $object->set('CODE', $code);
            $newCategory = new ItemCategory($object);
            $addRes = $newCategory->save();
            if ($addRes->isSuccess())
            {
                $categoryId = $newCategory->getId();
            }
            else
            {
                $result->addErrors($addRes->getErrors());
            }
        }

        return ($result)->setData(['ID' => $categoryId]);
    }

    /**
     * @param int $entityTypeId
     * @return int
     * @throws \Exception
     */
    private static function getDefaultCategoryId(int $entityTypeId): int
    {
        $category = ItemCategoryTable::query()
            ->setSelect(['ID'])
            ->setFilter(['=ENTITY_TYPE_ID' => $entityTypeId, 'IS_DEFAULT' => 'Y'])
            ->fetch();

        return !empty($category) ? (int)$category['ID'] : 0;
    }

    /**
     * @param int $entityTypeId
     * @param string $code
     * @return int
     * @throws \Exception
     */
    private static function getCategoryIdByCode(int $entityTypeId, string $code): int
    {
        $category = ItemCategoryTable::query()
            ->setSelect(['ID'])
            ->setFilter(['=ENTITY_TYPE_ID' => $entityTypeId, 'CODE' => $code])
            ->fetch();

        return !empty($category) ? (int)$category['ID'] : 0;
    }

    /**
     * @param int $entityTypeId
     * @param array $tunnels
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    private static function setupTunnels(int $entityTypeId, array $tunnels = []): Result
    {
        $result = new Result();
        $tunnelManager = new TunnelManager($entityTypeId);
        $userId = !empty($GLOBALS['USER']) ? CurrentUser::get()->getId() : 1;

        $removeResult = $tunnelManager->removeAllEntityTunnels($userId, $entityTypeId);
        if ($removeResult->isSuccess())
        {
            foreach ($tunnels as $tunnel)
            {
                $dstCategoryId = static::getCategoryIdByCode($entityTypeId, $tunnel['DST_CATEGORY_CODE']);
                $addResult = $tunnelManager->addTunnel(
                    $userId,
                    $tunnel['SRC_CATEGORY_ID'],
                    static::getStatusPrefix($entityTypeId, $tunnel['SRC_CATEGORY_ID']) . $tunnel['SRC_STATUS_CODE'],
                    $dstCategoryId,
                    static::getStatusPrefix($entityTypeId, $dstCategoryId) . $tunnel['DST_STATUS_CODE'],
                    TunnelManager::ROBOT_ACTION_MOVE
                );
                if (!$addResult->isSuccess())
                {
                    $result->addErrors($addResult->getErrors());
                    break;
                }
            }
        }
        else
        {
            $result->addErrors($removeResult->getErrors());
        }
        return $result;
    }

    /**
     * @return array
     */
    private static function getCategoriesData(): array
    {
        return [
            [
                'TITLE'      => Constants::DYNAMIC_CATEGORY_DEFAULT_TITLE,
                'CODE'       => Constants::DYNAMIC_CATEGORY_DEFAULT_CODE,
                'IS_DEFAULT' => 'Y',
                'STATUSES'   => [
                    Constants::DYNAMIC_STAGE_DEFAULT_NEW => [
                        "NAME"      => 'New',
                        "SORT"      => 10,
                        "COLOR"     => "#47E4C2",
                        "SEMANTICS" => PhaseSemantics::PROCESS,
                    ],
                    Constants::DYNAMIC_STAGE_DEFAULT_MY_STAGE => [
                        "NAME"      => 'My status',
                        "SORT"      => 20,
                        "COLOR"     => "#456078",
                        "SEMANTICS" => PhaseSemantics::PROCESS,
                    ],
                    Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS => [
                        "NAME"      => 'Success',
                        "SORT"      => 50,
                        "COLOR"     => "#90EE90",
                        "SEMANTICS" => PhaseSemantics::SUCCESS,
                    ],
                    Constants::DYNAMIC_STAGE_DEFAULT_FAIL => [
                        "NAME"      => 'Fail',
                        "SORT"      => 60,
                        "COLOR"     => "#F1361B",
                        "SEMANTICS" => PhaseSemantics::FAILURE,
                    ],
                ],
                "TUNNELS" => [
                    [
                        'SRC_STATUS_CODE'   => Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS,
                        'DST_CATEGORY_CODE' => Constants::DYNAMIC_CATEGORY_CUSTOM_CODE,
                        'DST_STATUS_CODE'   => Constants::DYNAMIC_STAGE_CUSTOM_NEW,
                        'ACTION'            => TunnelManager::ROBOT_ACTION_MOVE
                    ],
                    [
                        'SRC_STATUS_CODE'   => Constants::DYNAMIC_STAGE_DEFAULT_FAIL,
                        'DST_CATEGORY_CODE' => Constants::DYNAMIC_CATEGORY_CUSTOM_CODE,
                        'DST_STATUS_CODE'   => Constants::DYNAMIC_STAGE_CUSTOM_FAIL,
                        'ACTION'            => TunnelManager::ROBOT_ACTION_MOVE
                    ]
                ]
            ],
            [
                'TITLE'      => Constants::DYNAMIC_CATEGORY_CUSTOM_TITLE,
                'CODE'       => Constants::DYNAMIC_CATEGORY_CUSTOM_CODE,
                'IS_DEFAULT' => 'N',
                'STATUSES'   => [
                    Constants::DYNAMIC_STAGE_CUSTOM_NEW => [
                        "NAME"      => 'New',
                        "SORT"      => 10,
                        "COLOR"     => "#47E4C2",
                        "SEMANTICS" => PhaseSemantics::PROCESS,
                    ],
                    Constants::DYNAMIC_STAGE_CUSTOM_MY_STAGE => [
                        "NAME"      => 'My status',
                        "SORT"      => 20,
                        "COLOR"     => "#456078",
                        "SEMANTICS" => PhaseSemantics::PROCESS,
                    ],
                    Constants::DYNAMIC_STAGE_CUSTOM_SUCCESS => [
                        "NAME"      => 'Success',
                        "SORT"      => 50,
                        "COLOR"     => "#90EE90",
                        "SEMANTICS" => PhaseSemantics::SUCCESS,
                    ],
                    Constants::DYNAMIC_STAGE_CUSTOM_FAIL => [
                        "NAME"      => 'Fail',
                        "SORT"      => 60,
                        "COLOR"     => "#F1361B",
                        "SEMANTICS" => PhaseSemantics::FAILURE,
                    ],
                ],
            ],
        ];
    }
}
