<?php

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
