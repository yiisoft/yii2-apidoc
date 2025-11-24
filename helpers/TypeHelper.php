<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AggregatedType;

/**
 * An auxiliary class for working with types.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class TypeHelper
{
    /**
     * @return string[]
     */
    public static function splitType(?Type $type): array
    {
        if ($type === null) {
            return [];
        }

        // TODO: Don't split the Intersection
        if (!$type instanceof AggregatedType) {
            return [(string) $type];
        }

        $types = [];
        foreach ($type as $childType) {
            $types[] = (string) $childType;
        }

        return $types;
    }
}
