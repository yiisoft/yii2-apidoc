<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

/**
 * Description.
 */
class OffsetAccessTypes
{
    /**
     * Description.
     *
     * @template TArray of array<string, string>
     * @template TKey of string
     *
     * @param TArray $array
     * @param TKey $key
     *
     * @return (TKey is key-of<TArray> ? TArray[TKey] : null)
     */
    public function getSomeField($array, $key)
    {
    }
}
