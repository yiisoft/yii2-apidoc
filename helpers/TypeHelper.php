<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\PseudoTypes\Conditional;
use phpDocumentor\Reflection\PseudoTypes\ConditionalForParameter;
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
     * @return Type[]
     */
    public static function getTypesByAggregatedType(AggregatedType $compound): array
    {
        $types = [];
        foreach ($compound as $type) {
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @param Conditional|ConditionalForParameter $type
     * @return Type[] Possible unique types.
     */
    public static function getPossibleTypesByConditionalType(Type $type): array
    {
        $types = [];

        foreach ([$type->getIf(), $type->getElse()] as $innerType) {
            if ($innerType instanceof Conditional || $innerType instanceof ConditionalForParameter) {
                $types = array_merge($types, self::getPossibleTypesByConditionalType($innerType));
            } elseif ($innerType instanceof AggregatedType) {
                $types = array_merge($types, self::getTypesByAggregatedType($innerType));
            } else {
                $types[] = $innerType;
            }
        }

        $uniqueTypes = [];
        foreach ($types as $innerType) {
            $uniqueTypes[(string) $innerType] = $innerType;
        }

        return array_values($uniqueTypes);
    }
}
