<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

use yiiunit\apidoc\data\api\interfaces\BaseInterface;
use Exception;

/**
 * Description.
 *
 * @template TFirst
 * @template TSecond
 */
class GenericTypes
{
    public const COLOR_GREY = 'grey';
    public const COLOR_WHITE = 'white';

    public const COLORS = [
        self::COLOR_GREY,
        self::COLOR_WHITE,
    ];

    /** @var list description */
    public $listWithoutType;
    /** @var list<string> description */
    public $listWithType;
    /** @var array description */
    public $arrayWithoutGenerics;
    /** @var array<string> description */
    public $arrayWithValueType;
    /** @var array<string, array<array-key, mixed>> description */
    public $arrayWithKeyAndValueType;
    /** @var non-empty-list description */
    public $nonEmptyListWithoutType;
    /** @var non-empty-list<string> description */
    public $nonEmptyListWithType;
    /** @var non-empty-array description */
    public $nonEmptyArrayWithoutGenerics;
    /** @var non-empty-array<string> description */
    public $nonEmptyArrayWithValueType;
    /** @var non-empty-array<string, string> description */
    public $nonEmptyArrayWithKeyAndValueType;
    /** @var class-string description */
    public $classStringWithoutType;
    /** @var class-string<GenericTypes> description */
    public $classStringWithType;
    /** @var interface-string description */
    public $interfaceStringWithoutType;
    /** @var interface-string<BaseInterface> description */
    public $interfaceStringWithType;
    /** @var int<1, max> description */
    public $intRange;
    /** @var iterable description */
    public $iterableWithoutGenerics;
    /** @var iterable<int, string> description */
    public $iterableWithGenerics;
    /** @var key-of<self::COLORS> description */
    public $keyOf;
    /** @var value-of<self::COLORS> description */
    public $valueOf;
    /** @var int-mask<1, 2, 4> description */
    public $intMask;
    /** @var int-mask-of<1|2|4> description */
    public $intMaskOf;
    /** @var private-properties-of<GenericTypes> description */
    public $privatePropertiesOf;
    /** @var protected-properties-of<GenericTypes> description */
    public $protectedPropertiesOf;
    /** @var public-properties-of<GenericTypes> description */
    public $publicPropertiesOf;
    /** @var properties-of<GenericTypes> description */
    public $propertiesOf;
    /** @var GenericTypes<int, string>[] description */
    public $arrayOfGenericClasses;

    /**
     * Description.
     */
    public function getSelfWithoutGenerics(): self
    {
    }

    /**
     * Description.
     *
     * @return self<int, Exception>
     */
    public function getSelfWithGenerics(): self
    {
    }

    /**
     * Description.
     */
    public function getStaticWithoutGenerics(): static
    {
    }

    /**
     * Description.
     *
     * @return static<int, Exception>
     */
    public function getStaticWithGenerics(): static
    {
    }

    /**
     * Description.
     *
     * @return GenericTypes<int, Exception>
     */
    public function getClassWithGenerics(): GenericTypes
    {
    }
}
