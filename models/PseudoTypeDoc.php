<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use yii\base\BaseObject;

/**
 * Represents API documentation information for a `@phpstan-type` and `@psalm-type`.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 * @immutable
 */
class PseudoTypeDoc extends BaseObject
{
    public const TYPE_PHPSTAN = 'phpstan';
    public const TYPE_PSALM = 'phpstan';

    public string $type;

    public BaseDoc $parent;

    public string $name;

    public string $value;

    public function __construct(
        string $type,
        BaseDoc $parent,
        string $name,
        string $value
    ) {
        $this->type = $type;
        $this->parent = $parent;
        $this->name = $name;
        $this->value = $value;
    }
}
