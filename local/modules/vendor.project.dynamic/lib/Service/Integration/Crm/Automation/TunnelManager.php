<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * gpnsm - TunnelManager.php
 * 10.12.2022 21:14
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Service\Integration\Crm\Automation;

use Bitrix\Main\Result;

/**
 * Class TunnelManager
 * @package Vendor\Project\Dynamic\Service\Integration\Crm\Automation
 */
class TunnelManager extends \Bitrix\Crm\Automation\TunnelManager
{
    /**
     * @param int $userId
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     */
    public function removeAllEntityTunnels(int $userId, int $entityTypeId): Result
    {
        $result = new Result;
        $scheme = $this->getScheme();
        if (!empty($scheme['stages']))
        {
            foreach($scheme['stages'] as $stage)
            {
                if(!empty($stage['tunnels']))
                {
                    foreach($stage['tunnels'] as $tunnel)
                    {
                        $res = $this->removeTunnel($userId, $tunnel);
                        if(!$res->isSuccess())
                        {
                            $result->addErrors($res->getErrors());
                        }
                    }
                }
            }
        }
        return $result;
    }
}