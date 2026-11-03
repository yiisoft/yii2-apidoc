<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support;

use RuntimeException;
use yii\apidoc\models\Context;

class UnexpectedFullContext extends Context
{
    public function __wakeup(): void
    {
        throw new RuntimeException('Guide generation must not load the full context when a compact cache exists.');
    }
}
