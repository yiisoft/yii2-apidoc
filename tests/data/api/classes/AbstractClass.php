<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

use yiiunit\apidoc\data\api\interfaces\BaseInterface;
use yiiunit\apidoc\data\api\interfaces\SubInterface;

/**
 * Description.
 *
 * @author Test Author <test@test.com>
 * @since 2.0.0 with description
 * @package SomePackage
 *
 * @see BaseInterface
 * @see SubInterface
 */
abstract class AbstractClass implements BaseInterface
{
    /**
     * Description.
     * @return $this
     */
    abstract public function abstractMethod();
}
