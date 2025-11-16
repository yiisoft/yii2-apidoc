<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use InvalidArgumentException;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use yii\apidoc\helpers\PhpDocTagParser;
use yiiunit\apidoc\TestCase;

class PhpDocTagParserTest extends TestCase
{
    private PhpDocTagParser $phpDocTagParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpDocTagParser = new PhpDocTagParser();
    }

    /**
     * @dataProvider provideGetTypeFromMethodTagData
     */
    public function testGetTypeFromMethodTag(string $tag, ?string $expectedResult): void
    {
        $result = $this->phpDocTagParser->getTypeFromMethodTag($tag);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideGetTypeFromMethodTagData(): array
    {
        return [
            'method with return type' => [
                '@method array<string, mixed> asArray()',
                'array<string, mixed>',
            ],
            'method without return type' => [
                '@method asArray()',
                null,
            ],
        ];
    }

    public function testGetTypeFromMethodTagWithReturnTag(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Tag (@return string) is not @method'));
        $this->phpDocTagParser->getTypeFromMethodTag('@return string');
    }

    public function testGetTypeFromMethodTagWithInvalidTag(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Tag (@metho asArray()) is not @method'));
        $this->phpDocTagParser->getTypeFromMethodTag('@metho asArray()');
    }

    public function testGetTypeFromReturnTag(): void
    {
        $result = $this->phpDocTagParser->getTypeFromReturnTag('@return object{someKey: string}');
        $this->assertSame('object{someKey: string}', $result);
    }

    public function testGetTypeFromReturnTagWithMethodTag(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Tag (@method asArray()) is not @return'));
        $this->phpDocTagParser->getTypeFromReturnTag('@method asArray()');
    }

    public function testGetTypeFromReturnTagWithInvalidTag(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Tag (@retur int) is not @return'));
        $this->phpDocTagParser->getTypeFromReturnTag('@retur int');
    }

    public function testParseTag(): void
    {
        $parseReturnResult = $this->phpDocTagParser->parseTag('@return int');
        $this->assertNotNull($parseReturnResult);
        $this->assertInstanceOf(ReturnTagValueNode::class, $parseReturnResult->value);
        $this->assertSame('int', (string) $parseReturnResult->value->type);

        $parseVarResult = $this->phpDocTagParser->parseTag('@var key-of<self::SOME_CONSTANT>');
        $this->assertNotNull($parseVarResult);
        $this->assertInstanceOf(VarTagValueNode::class, $parseVarResult->value);
        $this->assertSame('key-of<self::SOME_CONSTANT>', (string) $parseVarResult->value->type);

        $parseEmptyStringResult = $this->phpDocTagParser->parseTag('');
        $this->assertNull($parseEmptyStringResult);
    }
}
