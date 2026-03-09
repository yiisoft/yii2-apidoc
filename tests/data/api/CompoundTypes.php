<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

use Exception;

/**
 * Description.
 */
class CompoundTypes
{
    /** description */
    public int|string|null|Exception|CompoundTypes $typeHint;
    /** @var int|string|null|Exception|CompoundTypes description */
    public $docType;
}
