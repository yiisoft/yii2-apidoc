<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Type;
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
    public const TYPE_PSALM = 'psalm';

    public const TYPES = [
        self::TYPE_PHPSTAN,
        self::TYPE_PSALM,
    ];

    /** @var value-of<self::TYPES> */
    public string $type;

    public BaseDoc $parent;

    public string $name;

    public Type $value;

    /**
     * @param value-of<self::TYPES> $type
     */
    public function __construct(
        string $type,
        BaseDoc $parent,
        string $name,
        Type $value
    ) {
        $this->type = $type;
        $this->parent = $parent;
        $this->name = $name;
        $this->value = $value;
    }
}
