<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

use Closure;

/**
 * Description.
 */
class CallableTypes
{
    /**
     * Description.
     */
    public function callableTypeHint(callable $typeHint): callable
    {
    }

    /**
     * Description.
     */
    public function closureTypeHint(Closure $typeHint): Closure
    {
    }

    /**
     * Description.
     *
     * @param callable $docType
     * @return callable
     */
    public function callableDocType($docType)
    {
    }

    /**
     * Description.
     *
     * @param callable(string, int&...$a=, CallableTypes&...=): void $docType
     * @return callable(string, int&...$a=, CallableTypes&...=): void
     */
    public function advancedCallableDocType($docType)
    {
    }

    /**
     * Description.
     *
     * @param Closure $docType
     * @return Closure
     */
    public function closureDocType($docType)
    {
    }

    /**
     * Description.
     *
     * @param Closure(string, int&...$a=, CallableTypes&...=): void $docType
     * @return Closure(string, int&...$a=, CallableTypes&...=): void
     */
    public function advancedClosureDocType($docType)
    {
    }
}
