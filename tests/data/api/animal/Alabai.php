<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Some description.
 */
class Alabai extends Dog
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        return 'It\'s a Alabai';
    }
}
