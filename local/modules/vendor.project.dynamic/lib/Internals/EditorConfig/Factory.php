<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - Factory.php
 * 19.02.2023 18:49
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\EditorConfig;

use Exception;
use Vendor\Project\Dynamic\Internals\Contract\IEditorConfig;
use Vendor\Project\Dynamic\Internals\EditorConfig\Scheme\CategoryOneConfig;
use Vendor\Project\Dynamic\Internals\EditorConfig\Scheme\CategoryTwoConfig;

/**
 * @class Factory
 * @package Vendor\Project\Dynamic\Internals\EditorConfig
 */
class Factory
{
    protected static ?Factory $instance = null;
    protected int    $entityTypeId;
    protected int    $typeId;

    /**
     * @param int $typeId
     * @param int $entityTypeId
     */
    protected function __construct(int $typeId, int $entityTypeId)
    {
        $this->typeId       = $typeId;
        $this->entityTypeId = $entityTypeId;
    }

    /**
     * @param int $typeId
     * @param int $entityTypeId
     * @return \Vendor\Project\Dynamic\Internals\EditorConfig\Factory|null
     */
    public static function getInstance(int $typeId, int $entityTypeId): ?Factory
    {
        if (static::$instance === null)
        {
            static::$instance = new static($typeId, $entityTypeId);
        }
        return static::$instance;
    }
    private function __clone(){}
    public function __wakeup(){}

    /**
     * @param string $configType
     * @return \Vendor\Project\Dynamic\Internals\Contract\IEditorConfig|null
     * @throws \Exception
     */
    public function createConfig(string $configType): ?IEditorConfig
    {
        $config = null;

        if (ConfigType::isTypeSupported($configType))
        {
            switch ($configType)
            {
                case ConfigType::CATEGORY_1:
                    $config = new CategoryOneConfig($this->typeId, $this->entityTypeId);
                    break;
                case ConfigType::CATEGORY_2:
                    $config = new CategoryTwoConfig($this->typeId, $this->entityTypeId);
                    break;
            }

            return $config;
        }
        else
        {
            throw new Exception("Can not resolve config by type '$configType'");
        }
    }
}
