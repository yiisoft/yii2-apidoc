<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

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
}
