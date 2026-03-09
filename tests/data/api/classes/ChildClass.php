<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

/**
 * @inheritdoc
 *
 * Additional description.
 */
class ChildClass extends BaseClass
{
    /**
     * {@inheritdoc}
     */
    public array $baseClassProperty;

    /**
     * {@inheritdoc}
     */
    public string $childProperty;

    /**
     * {@inheritdoc}
     */
    public function __construct($param, array $config = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function baseMethod(int $firstParam, string $secondParam): void
    {
        parent::baseMethod($firstParam, $secondParam);
    }

    /**
     * {@inheritdoc}
     */
    public function childMethod($param)
    {
    }
}
