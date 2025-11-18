<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use yii\apidoc\helpers\PhpDocParser;
use yiiunit\apidoc\TestCase;

class PhpDocParserTest extends TestCase
{
    private PhpDocParser $phpDocParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpDocParser = new PhpDocParser();
    }

    public function testParseType(): void
    {
        $parseIntResult = $this->phpDocParser->parseType('int');
        $this->assertInstanceOf(IdentifierTypeNode::class, $parseIntResult);
    }

    public function testParseTag(): void
    {
        $parseReturnResult = $this->phpDocParser->parseTag('@return int');
        $this->assertNotNull($parseReturnResult);

        /** @var ReturnTagValueNode|PhpDocTagValueNode */
        $parseReturnResultValue = $parseReturnResult->value;
        $this->assertInstanceOf(ReturnTagValueNode::class, $parseReturnResultValue);
        $this->assertSame('int', (string) $parseReturnResultValue->type);

        $parseVarResult = $this->phpDocParser->parseTag('@var key-of<self::SOME_CONSTANT>');
        $this->assertNotNull($parseVarResult);

        /** @var ReturnTagValueNode|PhpDocTagValueNode */
        $parseVarResultValue = $parseVarResult->value;
        $this->assertInstanceOf(VarTagValueNode::class, $parseVarResultValue);
        $this->assertSame('key-of<self::SOME_CONSTANT>', (string) $parseVarResultValue->type);
    }
}
