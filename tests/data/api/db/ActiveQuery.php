<?php

namespace yiiunit\apidoc\data\api\db;

/**
 * ActiveQuery represents a DB query associated with an Active Record class.
 *
 * @template T of (ActiveRecord|array)
 *
 * @method T|null one($db = null)
 * @method T[] all($db = null)
 * @method ($value is true ? (T is array ? static<T> : static<array<string, mixed>>) : static<T>) asArray($value = true)
 * @method BatchQueryResult<int, T[]> batch($batchSize = 100, $db = null)
 * @method BatchQueryResult<int, T> each($batchSize = 100, $db = null)
 */
class ActiveQuery implements ActiveQueryInterface
{
    /**
     * @return T|null
     */
    public function one()
    {
    }

    /**
     * @return T[]
     */
    public function all()
    {
    }
}
