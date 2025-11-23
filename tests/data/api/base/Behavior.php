<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\base;

/**
 * Behavior is the base class for all behavior classes.
 *
 * A behavior can be used to enhance the functionality of an existing component without modifying its code.
 * In particular, it can "inject" its own methods and properties into the component
 * and make them directly accessible via the component. It can also respond to the events triggered in the component
 * and thus intercept the normal code execution.
 *
 * @template T of Component
 */
class Behavior
{
    /**
     * @var T|null the owner of this behavior
     */
    public $owner;

    /**
     * Attaches the behavior object to the component.
     * @param T $owner the component that this behavior is to be attached to.
     */
    public function attach($owner)
    {
        $this->owner = $owner;
    }
}
