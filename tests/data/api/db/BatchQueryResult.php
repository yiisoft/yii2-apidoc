<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\db;

use yii\base\Component;

/**
 * BatchQueryResult represents a batch query from which you can retrieve data in batches.
 *
 * @implements \Iterator<int, mixed>
 */
class BatchQueryResult extends Component implements \Iterator
{
    /**
     * Resets the iterator to the initial state.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function rewind() {}

    /**
     * Moves the internal pointer to the next dataset.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function next() {}

    /**
     * Returns the index of the current dataset.
     * This method is required by the interface [[\Iterator]].
     * @return int the index of the current row.
     */
    #[\ReturnTypeWillChange]
    public function key() {}

    /**
     * Returns the current dataset.
     * This method is required by the interface [[\Iterator]].
     * @return mixed the current dataset.
     */
    #[\ReturnTypeWillChange]
    public function current() {}

    /**
     * Returns whether there is a valid dataset at the current position.
     * This method is required by the interface [[\Iterator]].
     * @return bool whether there is a valid dataset at the current position.
     */
    #[\ReturnTypeWillChange]
    public function valid() {}
}
