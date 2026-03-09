<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

/**
 * Description.
 *
 * @template TArray of array<array-key, mixed>
 */
class Templates
{
    /** @var TArray[] description */
    public $arrays;

    /**
     * Description.
     *
     * @template TKey of array-key
     * @param TKey $key
     * @return (TKey is key-of<TArray> ? TArray[TKey] : null)
     */
    public function getSomeField($key)
    {
    }
}
