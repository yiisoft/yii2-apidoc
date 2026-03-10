<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\traits;

/**
 * Description.
 */
trait BaseTrait
{
    public const BASE_TRAIT_CONST = 'BASE_TRAIT_CONST';

    /** description */
    public int $traitProp;

    /**
     * Description.
     */
    public function baseTraitMethod(): void
    {
    }
}
