<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Dog represents a dog animal.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.1
 */
class Dog extends Animal
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        // this method has `@inheritdoc` tag without brackets
        return 'This is a dog';
    }
}