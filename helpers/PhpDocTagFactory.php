<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use yii\apidoc\models\PlainType;

/**
 * An auxiliary class for creating PHPDoc tags.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class PhpDocTagFactory
{
    /**
     * Creates a tag that contains types (for example, {@link \phpDocumentor\Reflection\DocBlock\Tags\Return_})
     * based on the {@link \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode}.
     *
     * @throws InvalidArgumentException When a tag is not supported or does not contain types.
     */
    public function createTagWithTypesByTagNode(PhpDocTagNode $tagNode): Tag
    {
        $tagNodeValue = $tagNode->value;

        if ($tagNodeValue instanceof ParamTagValueNode) {
            return new Param(
                substr($tagNodeValue->parameterName, 1),
                $this->createPlainType($tagNodeValue->type),
                $tagNodeValue->isVariadic,
                $this->createDescription($tagNodeValue->description)
            );
        } elseif ($tagNodeValue instanceof ReturnTagValueNode) {
            return new Return_(
                $this->createPlainType($tagNodeValue->type),
                $this->createDescription($tagNodeValue->description)
            );
        } elseif ($tagNodeValue instanceof VarTagValueNode) {
            return new Var_(
                substr($tagNodeValue->variableName, 1),
                $this->createPlainType($tagNodeValue->type),
                $this->createDescription($tagNodeValue->description)
            );
        } elseif ($tagNodeValue instanceof PropertyTagValueNode) {
            return $this->createPropertyTag($tagNode);
        } elseif ($tagNodeValue instanceof MethodTagValueNode) {
            return $this->createMethodTag($tagNodeValue);
        } elseif ($tagNodeValue instanceof TemplateTagValueNode) {
            return new Template(
                $tagNodeValue->name,
                $this->createPlainType($tagNodeValue->bound),
                $this->createPlainType($tagNodeValue->default),
                $this->createDescription($tagNodeValue->description)
            );
        }

        throw new InvalidArgumentException("Unsupported tag ({$tagNodeValue})");
    }

    private function createDescription(string $text): Description
    {
        return new Description($text);
    }

    private function createPlainType(?TypeNode $typeNode): ?PlainType
    {
        if ($typeNode === null) {
            return null;
        }

        return new PlainType((string) $typeNode);
    }

    /**
     * @return PropertyRead|PropertyWrite|Property
     */
    private function createPropertyTag(PhpDocTagNode $tagNode): TagWithType
    {
        $tagNodeValue = $tagNode->value;
        $propertyNameWithoutDollar = substr($tagNodeValue->propertyName, 1);

        switch ($tagNode->name) {
            case '@property-read':
                return new PropertyRead(
                    $propertyNameWithoutDollar,
                    $this->createPlainType($tagNodeValue->type),
                    $this->createDescription($tagNodeValue->description)
                );

            case '@property-write':
                return new PropertyWrite(
                    $propertyNameWithoutDollar,
                    $this->createPlainType($tagNodeValue->type),
                    $this->createDescription($tagNodeValue->description)
                );

            default:
                return new Property(
                    $propertyNameWithoutDollar,
                    $this->createPlainType($tagNodeValue->type),
                    $this->createDescription($tagNodeValue->description)
                );
        }
    }

    private function createMethodTag(MethodTagValueNode $tagNodeValue): Method
    {
        $methodParameters = array_map(function (MethodTagValueParameterNode $parameter) {
            $defaultValue = $parameter->defaultValue !== null
                ? (string) $parameter->defaultValue
                : MethodParameter::NO_DEFAULT_VALUE;

            return new MethodParameter(
                substr($parameter->parameterName, 1),
                $this->createPlainType($parameter->type),
                $parameter->isReference,
                $parameter->isVariadic,
                $defaultValue
            );
        }, $tagNodeValue->parameters);

        return new Method(
            $tagNodeValue->methodName,
            [],
            $this->createPlainType($tagNodeValue->returnType),
            $tagNodeValue->isStatic,
            $this->createDescription($tagNodeValue->description),
            false,
            $methodParameters
        );
    }
}
