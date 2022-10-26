<?php
namespace Vendor\Project\Basic\Agent;

use CAgent;

/**
 * Class AgentManager
 * @package Vendor\Project\Basic\Agent
 */
class AgentManager
{
    protected static array $agents = [];
    protected static ?AgentManager $instance = null;

    private function __construct(){
        static::$agents = $this->getAgentsData();
    }

    public static function getInstance(): AgentManager
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function addAgents(): bool
    {
        foreach (static::$agents as $agent)
        {
            CAgent::AddAgent(
                $agent['handler'],
                $agent['module'],
                $agent['period'],
                $agent['interval'],
                $agent['dateCheck'],
                $agent['active'],
                $agent['nextExec']
            );
        }
        return true;
    }

    public function removeAgents(): bool
    {
        CAgent::RemoveModuleAgents(GetModuleID(__FILE__));
        return true;
    }

    protected function getAgentsData(): array
    {
        return [
            [
                'handler'   => "\Vendor\Project\Basic\Agent\Common::exampleAgentFunction();",
                'module'    => GetModuleID(__FILE__),
                'period'    => "N",
                'interval'  => 86400,
                'dateCheck' => date("d.m.Y", time() + 86400)." 06:01:00",
                'active'    => 'Y',
                'nextExec'  => date("d.m.Y", time() + 86400)." 06:00:00",
            ]
        ];
    }

    private function __clone(){}
    public function __wakeup(){}
}