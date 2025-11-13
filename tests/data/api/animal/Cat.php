<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Cat represents a cat animal.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.1
 */
class Cat extends Animal
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // this method has `inheritdoc` tag in brackets
        return 'This is a cat';
    }

    public function methodWithoutDocAndTypeHints()
    {
        return '';
    }
}
