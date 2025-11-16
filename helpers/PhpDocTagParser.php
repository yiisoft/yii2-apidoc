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
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Throwable;

/**
 * An auxiliary class for working with PHPDoc tags.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class PhpDocTagParser
{
    private Lexer $lexer;

    private PhpDocParser $phpDocParser;

    /** @var array<string, Throwable> */
    private array $exceptions = [];

    /**
     * @param array{lines?: bool, indexes?: bool, comments?: bool} $usedAttributes
     */
    public function __construct(array $usedAttrubutes = [])
    {
        $config = new ParserConfig($usedAttrubutes);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);

        $this->lexer = new Lexer($config);
        $this->phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
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

    /**
     * @throws InvalidArgumentException When the input tag is not method.
     */
    public function getTypeFromMethodTag(string $tag): ?string
    {
        $parsedTag = $this->parseTag($tag);
        if (!$parsedTag->value instanceof MethodTagValueNode) {
            throw new InvalidArgumentException("Tag ({$tag}) is not @method");
        }

        return $parsedTag->value->returnType !== null ? (string) $parsedTag->value->returnType : null;
    }

    /**
     * @throws InvalidArgumentException When the input tag is not return.
     */
    public function getTypeFromReturnTag(string $tag): string
    {
        $parsedTag = $this->parseTag($tag);
        if (!$parsedTag->value instanceof ReturnTagValueNode) {
            throw new InvalidArgumentException("Tag ({$tag}) is not @return");
        }

        return (string) $parsedTag->value->type;
    }

    public function parseTag(string $tag): ?PhpDocTagNode
    {
        $tokens = $this->getTokens($tag);

        try {
            return $this->phpDocParser->parseTag($tokens);
        } catch (Throwable $e) {
            $this->exceptions[$tag] = $e;

            return null;
        }
    }

    private function getTokens(string $string): TokenIterator
    {
        return new TokenIterator($this->lexer->tokenize($string));
    }
}
