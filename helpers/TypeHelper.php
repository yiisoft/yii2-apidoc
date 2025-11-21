<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;

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
    public static function getTypesByGenericTypeNode(GenericTypeNode $typeNode): array
    {
        return array_map(fn(TypeNode $childTypeNode) => (string) $childTypeNode, $typeNode->genericTypes);
    }

    /**
     * @return string[]
     */
    public static function getTypesByIntersectionTypeNode(IntersectionTypeNode $typeNode): array
    {
        return array_map(fn(TypeNode $childTypeNode) => (string) $childTypeNode, $typeNode->types);
    }

    /**
     * @param ConditionalTypeNode|ConditionalTypeForParameterNode $typeNode
     * @return string[] All possible unique types.
     */
    public static function getPossibleTypesByConditionalTypeNode(TypeNode $typeNode): array
    {
        return array_unique(self::getPossibleTypesByConditionalTypeNodeInternal($typeNode));
    }

    /**
     * @return string[]
     */
    public static function getTypesByArrayTypeNode(ArrayTypeNode $typeNode): array
    {
        if ($typeNode->type instanceof UnionTypeNode) {
            return array_map(fn(TypeNode $childTypeNode) => (string) $childTypeNode, $typeNode->type->types);
        }

        return [(string) $typeNode->type];
    }

    /**
     * @return string[]
     */
    public static function getTypesByUnionTypeNode(UnionTypeNode $typeNode): array
    {
        return array_map(fn(TypeNode $childTypeNode) => (string) $childTypeNode, $typeNode->types);
    }

    /**
     * @return string[]
     */
    public static function splitType(?Type $type): array
    {
        if ($type === null) {
            return [];
        }

        if (!$type instanceof Compound) {
            return [(string) $type];
        }

        $types = [];
        foreach ($type as $childType) {
            $types[] = (string) $childType;
        }

        return $types;
    }

    /**
     * @param ConditionalTypeForParameterNode|ConditionalTypeNode $typeNode
     * @return string[]
     */
    private static function getPossibleTypesByConditionalTypeNodeInternal(TypeNode $typeNode): array
    {
        $types = [];

        foreach ([$typeNode->if, $typeNode->else] as $innerType) {
            if ($innerType instanceof ConditionalTypeNode) {
                $types = array_merge($types, self::getPossibleTypesByConditionalTypeNodeInternal($innerType));
            } elseif ($innerType instanceof UnionTypeNode) {
                $types = array_merge($types, self::getTypesByUnionTypeNode($innerType));
            } else {
                $types[] = (string) $innerType;
            }
        }

        return $types;
    }
}
