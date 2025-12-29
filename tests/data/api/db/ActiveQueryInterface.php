<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\db;

interface ActiveQueryInterface
{
    public const FIRST_CONST = 'firstConst';
    public const SECOND_CONST = 'secondConst';

    public function one();
    public function all();
}
