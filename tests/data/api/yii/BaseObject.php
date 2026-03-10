<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * This is a kind of hack to check classes that extend `BaseObject`. The fact is that it is impossible
 * to use vendor in this case, because there will be a lot of unnecessary things in the snapshots,
 * and the snapshots will become dependent on the vendor.
 */
class BaseObject
{
}
