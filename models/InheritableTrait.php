<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 * Class InheritableTrait
 *
 * @author Brandon Kelly
 * @since 2.1.3
 */
trait InheritableTrait
{
    private $_inheritsFrom = [];

    /**
     * @param string $type
     */
    public function addInherit($type)
    {
        $this->_inheritsFrom[$type] = true;
    }

    /**
     * @return TypeDoc[]
     */
    public function getContext()
    {
        return array_merge([$this->definedBy], array_keys($this->_inheritsFrom));
    }
}
