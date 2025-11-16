<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use InvalidArgumentException;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Throwable;

/**
 * An auxiliary class for working with types.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class TypeAnalyzer
{
    private TypeParser $typeParser;

    private Lexer $lexer;

    /** @var array<string, Throwable> */
    private array $exceptions = [];

    /**
     * @param array{lines?: bool, indexes?: bool, comments?: bool} $usedAttributes
     */
    public function __construct(array $usedAttrubutes = [])
    {
        $config = new ParserConfig($usedAttrubutes);
        $constExprParser = new ConstExprParser($config);

        $this->typeParser = new TypeParser($config, $constExprParser);
        $this->lexer = new Lexer($config);
    }

    /**
     * @return array<string, Throwable>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function resetExceptions(): void
    {
        $this->exceptions = [];
    }

    public function isConditionalType(string $type): bool
    {
        $parsedType = $this->parseType($type);

        return $parsedType instanceof ConditionalTypeForParameterNode;
    }

    public function isGenericType(string $type): bool
    {
        $parsedType = $this->parseType($type);

        return $parsedType instanceof GenericTypeNode;
    }

    public function isIntersectionType(string $type): bool
    {
        $parsedType = $this->parseType($type);

        return $parsedType instanceof IntersectionTypeNode;
    }

    /**
     * @return string[]
     */
    public function getTypesByGenericType(string $type): array
    {
        $parsedType = $this->parseType($type);
        if (!$parsedType instanceof GenericTypeNode) {
            return [];
        }

        return array_map(fn(TypeNode $node) => (string) $node, $parsedType->genericTypes);
    }

    /**
     * @return string[]
     */
    public function getTypesByIntersectionType(string $type): array
    {
        $parsedType = $this->parseType($type);
        if (!$parsedType instanceof IntersectionTypeNode) {
            return [];
        }

        return array_map(fn(TypeNode $node) => (string) $node, $parsedType->types);
    }

    /**
     * @throws InvalidArgumentException When the input type is not conditional.
     * @return string[] All possible unique types.
     */
    public function getPossibleTypesByConditionalType(string $type): array
    {
        $parsedType = $this->parseType($type);
        if (!$parsedType instanceof ConditionalTypeForParameterNode) {
            throw new InvalidArgumentException("Type ({$type}) is not conditional");
        }

        $types = $this->getPossibleTypesByConditionalTypeInternal($parsedType);

        return array_unique($types);
    }

    /**
     * @throws InvalidArgumentException When the input type is not array.
     * @return string[]
     */
    public function getTypesByArrayType(string $type): array
    {
        $parsedType = $this->parseType($type);
        if (!$parsedType instanceof ArrayTypeNode) {
            throw new InvalidArgumentException("Type ({$type}) is not array");
        }

        if ($parsedType->type instanceof UnionTypeNode) {
            return array_map(fn(TypeNode $typeNode) => (string) $typeNode, $parsedType->type->types);
        }

        return [(string) $parsedType->type];
    }

    /**
     * @return string[]
     */
    public function getChildTypesByType(string $type): array
    {
        $parsedType = $this->parseType($type);
        if (!$parsedType instanceof UnionTypeNode) {
            return [(string) $parsedType];
        }

        return array_map(fn(TypeNode $typeNode) => (string) $typeNode, $parsedType->types);
    }

    private function parseType(string $type): ?TypeNode
    {
        $tokens = $this->getTokens($type);

        try {
            return $this->typeParser->parse($tokens);
        } catch (Throwable $e) {
            $this->exceptions[$type] = $e;

            return null;
        }
    }

    /**
     * @param ConditionalTypeForParameterNode|ConditionalTypeNode $typeNode
     *
     * @return string[]
     */
    private function getPossibleTypesByConditionalTypeInternal(TypeNode $typeNode): array
    {
        $types = [];

        if ($typeNode->if instanceof ConditionalTypeNode) {
            $types = array_merge($types, $this->getPossibleTypesByConditionalTypeInternal($typeNode->if));
        } else {
            $types = array_merge($types, $this->getChildTypesByType((string) $typeNode->if));
        }

        if ($typeNode->else instanceof ConditionalTypeNode) {
            $types = array_merge($types, $this->getPossibleTypesByConditionalTypeInternal($typeNode->else));
        } else {
            $types = array_merge($types, $this->getChildTypesByType((string) $typeNode->else));
        }

        return $types;
    }

    private function getTokens(string $string): TokenIterator
    {
        return new TokenIterator($this->lexer->tokenize($string));
    }
}
