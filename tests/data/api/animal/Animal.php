<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

use yii\base\BaseObject;

/**
 * Animal is a base class for animals.
 *
 * @property int $age animal age in seconds.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class Animal extends BaseObject
{
    /**
     * @var string animal name.
     */
    public $name;
    /**
     * @var int animal birth date as a UNIX timestamp.
     */
    public $birthDate;


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
}