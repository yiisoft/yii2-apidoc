<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Fqsen;
use yii\base\BaseObject;

/**
 * Represents API documentation information for a `@phpstan-import-type` and `@psalm-import-type`.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 * @immutable
 */
final class PseudoTypeImportDoc extends BaseObject
{
    public const TYPE_PHPSTAN = 'phpstan';
    public const TYPE_PSALM = 'psalm';

    /** @var value-of<self::TYPES> */
    public string $type;

    public string $typeName;

    public Fqsen $typeParentFqsen;

    /**
     * @param value-of<self::TYPES> $type
     */
    public function __construct(
        string $type,
        string $typeName,
        Fqsen $typeParentFqsen
    ) {
        $this->type = $type;
        $this->typeName = $typeName;
        $this->typeParentFqsen = $typeParentFqsen;
    }
}
