<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models\types;

use phpDocumentor\Reflection\Type;

/**
 * The Value object representing the conditional return type.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class ConditionalReturnType implements Type
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
