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
     * @event Some description for event tag.
     */
    public const EVENT_BEGIN_PAGE = 'beginPage';

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // this method has `inheritdoc` tag in brackets
        return 'This is a cat';
    }

    public function methodWithoutDocAndTypeHints($param)
    {
        return $param;
    }

    /**
     * @todo Some description for todo tag.
     */
    public function methodWithTodoTag(): void
    {
    }
}
