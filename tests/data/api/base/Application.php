<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\base;

/**
 * Application is the base class for all application classes.
 */
abstract class Application
{
    /**
     * @var Action<covariant Controller>|null the requested Action. If null, it means the request cannot be resolved into an action.
     */
    public $requestedAction;
}
