<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Cat represents a cat animal.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Cat extends Animal
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return 'This is a cat';
    }
}