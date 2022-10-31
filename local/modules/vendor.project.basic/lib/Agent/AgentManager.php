<?php
namespace Vendor\Project\Basic\Agent;

use CAgent;

/**
 * Class AgentManager
 * @package Vendor\Project\Basic\Agent
 */
class AgentManager
{
    protected array $agents = [];
    protected string $moduleId;
    protected static ?AgentManager $instance = null;

    private function __construct(){
        $this->agents   = $this->getAgentsData();
        $this->moduleId = GetModuleID(__FILE__);
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
        foreach ($this->agents as $agent)
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
        CAgent::RemoveModuleAgents($this->moduleId);
        return true;
    }

    protected function getAgentsData(): array
    {
        return [
            [
                'handler'   => "\Vendor\Project\Basic\Agent\Common::exampleAgentFunction();",
                'module'    => $this->moduleId,
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