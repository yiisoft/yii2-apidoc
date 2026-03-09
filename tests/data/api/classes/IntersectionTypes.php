<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

/**
 * Description.
 */
class IntersectionTypes
{
    /** @var IntersectionTypes&InlineTags description */
    public $basic;
    /** @var IntersectionTypes&GenericTypes<int, string> description */
    public $withGenerics;
}
