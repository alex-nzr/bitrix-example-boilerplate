<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * gpnsm - IEditorConfig.php
 * 19.02.2023 16:03
 * ==================================================
 */
namespace Vendor\Project\Dynamic\Internals\Contract;

/**
 * Interface IEditorConfig
 * @package Vendor\Project\Dynamic\Internals\Contract
 */
interface IEditorConfig
{
    /**
     * @return array
     */
    public function getEditorConfigTemplate(): array;

    /**
     * @return array
     */
    public function getHiddenFields(): array;

    /**
     * @return array
     */
    public function getReadonlyFields(): array;

    /**
     * @return array
     */
    public function getRequiredFields(): array;
}
