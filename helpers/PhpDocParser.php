<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser as PhpStanPhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * An auxiliary class for parsing PHPDoc.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class PhpDocParser
{
    private TypeParser $typeParser;

    private Lexer $lexer;

    private PhpStanPhpDocParser $phpStanPhpDocParser;

    /**
     * @param array{lines?: bool, indexes?: bool, comments?: bool} $usedAttributes
     */
    public function __construct(array $usedAttrubutes = [])
    {
        $config = new ParserConfig($usedAttrubutes);
        $constExprParser = new ConstExprParser($config);

        $this->typeParser = new TypeParser($config, $constExprParser);
        $this->lexer = new Lexer($config);
        $this->phpStanPhpDocParser = new PhpStanPhpDocParser($config, $this->typeParser, $constExprParser);
    }

    public function parseType(string $type): TypeNode
    {
        $tokens = $this->getTokens($type);

        return $this->typeParser->parse($tokens);
    }

    private function getTokens(string $string): TokenIterator
    {
        return new TokenIterator($this->lexer->tokenize($string));
    }
}
