<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

/**
 * Description.
 */
class ConditionalTypes
{
    /**
     * Description.
     *
     * @template T of object
     * @param string|class-string<T> $param
     * @return ($param is class-string<T> ? T : object)
     */
    public function conditionalForParameter($param)
    {
    }

    /**
     * Description.
     *
     * @template T of object|string
     * @param T $param
     * @return (T is object ? array<string, mixed> : mixed)
     */
    public function conditional($param)
    {
    }
}
