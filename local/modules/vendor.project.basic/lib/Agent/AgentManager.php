<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2022
 * ==================================================
 * example project - AgentManager.php
 * 24.11.2022 12:29
 * ==================================================
 */

namespace Vendor\Project\Basic\Agent;

use CAgent;
use Vendor\Project\Basic\Internals\Control\ServiceManager;

/**
 * Class AgentManager
 * @package Vendor\Project\Basic\Agent
 */
class AgentManager
{
    protected static ?AgentManager $instance = null;
    protected array $agents = [];

    /**
     * AgentManager constructor.
     */
    private function __construct(){
        $this->agents   = $this->getAgentsData();
    }

    /**
     * @return \Vendor\Project\Basic\Agent\AgentManager
     */
    public static function getInstance(): AgentManager
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return bool
     */
    public function addAgents(): bool
    {
        foreach ($this->agents as $agent)
        {
            CAgent::AddAgent(
                $agent['handler'],
                ServiceManager::getModuleId(),
                $agent['period'],
                $agent['interval'],
                $agent['dateCheck'],
                $agent['active'],
                $agent['nextExec']
            );
        }
        return true;
    }

    /**
     * @return bool
     */
    public function removeAgents(): bool
    {
        CAgent::RemoveModuleAgents(ServiceManager::getModuleId());
        return true;
    }

    /**
     * @return array
     */
    protected function getAgentsData(): array
    {
        return [
            /*[
                'handler'   => "\Vendor\Project\Basic\Agent\Common::someFunc();",
                'period'    => "N",
                'interval'  => 3600,
                'dateCheck' => date("d.m.Y H:i:s", time() + 3660),
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y H:i:s", time() + 3600),
            ],*/
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}