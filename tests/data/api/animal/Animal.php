<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

use Yii;
use yii\base\BaseObject;

/**
 * Animal is a base class for animals.
 *
 * @property int $age animal age in seconds.
 *
 * @method int getSomething($test)
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class Animal extends BaseObject
{
    public const COLOR_GREY = 'grey';
    public const COLOR_WHITE = 'white';

    public const COLORS = [
        self::COLOR_GREY,
        self::COLOR_WHITE,
    ];

    /**
     * @var string animal name.
     */
    public $name;
    /**
     * @var int animal birth date as a UNIX timestamp.
     */
    public $birthDate;

    // Supported PHPStan/Psalm syntax

    /**
     * @var (Cat|Dog)[]
     */
    public $arrayWithParenthesesProperty;
    /**
     * @var Dog[]|Cat[]
     */
    public $arrayWithoutParenthesesProperty;
    /**
     * @var int<0, max>
     */
    public $intRangeProperty;
    /**
     * @var array{someKey: string}
     */
    public $arrayShapeProperty;
    /**
     * @var object{someKey: string}
     */
    public $objectShapeProperty;
    /**
     * @var iterable<int, string>
     */
    public $iterableProperty;
    /**
     * @var array<string>
     */
    public $genericArrayWithoutKeyProperty;
    /**
     * @var array<array-key, array<string>>
     */
    public $genericArrayWithKeyProperty;
    /**
     * @var callable(mixed): bool
     */
    public $callableProperty;
    /**
     * @var \Closure(mixed): bool
     */
    public $closureProperty;
    /**
     * @var Cat&Dog
     */
    public $intersectionType;

    // Unsupported PHPStan/Psalm syntax
    // TODO: add support

    /**
     * @var int
     *
     * @phpstan-var int-mask<1, 2, 4>
     * @psalm-var int-mask<1, 2, 4>
     */
    public $intMaskPropery;
    /**
     * @var int
     *
     * @phpstan-var int-mask-of<1|2|4>
     * @psalm-var int-mask-of<1|2|4>
     */
    public $intMaskOfPropery;
    /**
     * @var string
     *
     * @phpstan-var value-of<self::COLORS>
     * @psalm-var value-of<self::COLORS>
     */
    public $valueOfProperty;
    /**
     * @var int
     *
     * @phpstan-var key-of<self::COLORS>
     * @psalm-var key-of<self::COLORS>
     */
    public $keyOfProperty;

    /**
     * Renders animal description.
     * @return string HTML output.
     */
    abstract public function render();

    /**
     * Returns animal age in seconds.
     * @return int animal age in seconds.
     */
    public function getAge()
    {
        return time() - $this->birthDate;
    }

    /**
     * Checks whether the animal is older than the specified time.
     * @param int $date date as a UNIX timestamp.
     * @return bool
     */
    public function isOlder($date)
    {
        return $this->getAge() > $date;
    }

    /**
     * @return array{name: string, birthDate: int}
     */
    public function asArray()
    {
        return [
            'name' => $this->name,
            'birthDate' => $this->birthDate,
        ];
    }

    /**
     * @return object{name: string, birthDate: int}
     */
    public function asStdClass()
    {
        return (object) $this->asArray();
    }

    /**
     * @return $this
     */
    public function setName(string $newName): self
    {
        $this->name = trim($newName);
        return $this;
    }

    public function setBirthDate(int $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return static
     */
    public static function getStatic()
    {
        return Yii::createObject([
            'class' => get_called_class(),
        ]);
    }
}
