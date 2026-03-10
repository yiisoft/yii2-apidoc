<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\interfaces;

/**
 * Description.
 *
 * @property string $interfaceProperty description
 */
interface BaseInterface
{
    public const BASE_INTERFACE_CONSTANT = 'BASE_INTERFACE_CONSTANT';

    /**
     * Description.
     */
    public function baseMethod(int $firstParam, string $secondParam): void;
}
