<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

/**
 * Description.
 */
class ShapeTypes
{
    /** @var array{name: string, value?: mixed} description */
    public $arrayWithStringKeys;
    /** @var array{0: string, 1?: mixed} description */
    public $arrayWithIntKeys;
    /** @var array{string, mixed} description */
    public $arrayWithoutKeys;
    /** @var list{string, int} description */
    public $listWithoutKeys;
    /** @var list{0: string, 1?: int} description */
    public $listWithKeys;
    /** @var object{name: string, value: mixed} description */
    public $object;
}
