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
class BasicTypes
{
    /** description */
    public int $intTypeHint;
    /** description */
    public string $stringTypeHint;
    /** description */
    public ?string $nullableTypeHint;
    /** description */
    public Exception $nativeClassTypeHint;
    /** description */
    public BasicTypes $userClassTypeHint;

    /** @var int description */
    public $intDocType;
    /** @var string description */
    public $stringDocType;
    /** @var string|null description */
    public $nullableDocType;
    /** @var Exception description */
    public $nativeClassDocType;
    /** @var BasicTypes description */
    public $userClassDocType;

    /** @var BasicTypes[] description */
    public $multidimensionalArray;
    /** @var (BasicTypes|int)[] description */
    public $multidimensionalArrayWithUnion;
}
