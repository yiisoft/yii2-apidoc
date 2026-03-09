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
class OtherTypes
{
    /** @var array-key description */
    public $arrayKey;
    /** @var scalar description */
    public $scalar;
    /** @var open-resource description */
    public $openResource;
    /** @var closed-resource description */
    public $closedResource;
    /** @var callable-string description */
    public $callableString;
    /** @var numeric-string description */
    public $numericString;
    /** @var non-empty-string description */
    public $nonEmptyString;
    /** @var non-falsy-string description */
    public $nonFalsyString;
    /** @var truthy-string description */
    public $truthyString;
    /** @var literal-string description */
    public $literalString;
    /** @var lowercase-string description */
    public $lowercaseString;
    /** @var positive-int description */
    public $positiveInt;
    /** @var negative-int description */
    public $negativeInt;
    /** @var non-positive-int description */
    public $nonPositiveInt;
    /** @var non-negative-int description */
    public $nonNegativeInt;
    /** @var non-zero-int description */
    public $nonZeroInt;
    /** @var trait-string description */
    public $traitString;
    /** @var enum-string description */
    public $enumString;
    /** @var callable-array description */
    public $callableArray;

    /**
     * Description.
     * @return never-return
     */
    public function neverReturn()
    {
    }

    /**
     * Description.
     * @return never-returns
     */
    public function neverReturns()
    {
    }

    /**
     * Description.
     * @return no-return
     */
    public function noReturn()
    {
    }

    /**
     * Description.
     * @return true
     */
    public function true()
    {
    }

    /**
     * Description.
     * @return false
     */
    public function false()
    {
    }

    /**
     * Description.
     * @return $this
     */
    public function getThis()
    {
    }
}
