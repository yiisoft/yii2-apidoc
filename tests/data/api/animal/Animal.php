<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

use yii\base\BaseObject;

/**
 * Animal is a base class for animals.
 *
 * @property int $age animal age in seconds.
 *
 * @method int getSomething($test, int $test2, int|string $test3)
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

    public string $propertyWithoutDoc = '';

    public $propertyWithoutDocAndTypeHint = '';

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

    public function setBirthDate(int $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }
}
