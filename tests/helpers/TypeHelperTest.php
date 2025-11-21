<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\ArrayKey;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use yii\apidoc\helpers\PhpDocParser;
use yii\apidoc\helpers\TypeHelper;
use yiiunit\apidoc\TestCase;

class TypeHelperTest extends TestCase
{
    private PhpDocParser $phpDocParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpDocParser = new PhpDocParser();
    }

    /**
     * @dataProvider provideSplitTypeData
     *
     * @param string[] $expectedResult
     */
    public function testSplitType(?Type $type, array $expectedResult): void
    {
        $result = TypeHelper::splitType($type);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{Type|null, string[]}>
     */
    public static function provideSplitTypeData(): array
    {
        return [
            'without type' => [
                null,
                [],
            ],
            'string' => [
                new String_(),
                ['string'],
            ],
            'integer' => [
                new Integer(),
                ['int'],
            ],
            'intersection' => [
                new Intersection([new Object_(new Fqsen('\Exception')), new Object_(new Fqsen('\SomeException'))]),
                ['\Exception&\SomeException'],
            ],
            'compound' => [
                new Compound([new Object_(new Fqsen('\Exception')), new Object_(new Fqsen('\SomeException'))]),
                ['\Exception', '\SomeException'],
            ],
            'array key' => [
                new ArrayKey(),
                ['array-key'],
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByGenericTypeNodeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByGenericTypeNode(string $type, array $expectedResult): void
    {
        $typeNode = $this->phpDocParser->parseType($type);
        $result = TypeHelper::getTypesByGenericTypeNode($typeNode);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}
     */
    public static function provideGetTypesByGenericTypeNodeData(): array
    {
        return [
            'generic array' => [
                'array<string, mixed>',
                ['string', 'mixed'],
            ],
            'generic class' => [
                'Action<Controller>',
                ['Controller'],
            ],
            'nested generics' => [
                'static<array<string, mixed>>',
                ['array<string, mixed>'],
            ],
            'multiple nested generics' => [
                'static<array<string, mixed>, Action<Controller>>',
                ['array<string, mixed>', 'Action<Controller>'],
            ],
        ];
    }

    /**
     * @dataProvider provideGetPossibleTypesByConditionalTypeNodeData
     *
     * @param string[] $expectedResult
     */
    public function testGetPossibleTypesByConditionalType(string $type, array $expectedResult): void
    {
        $typeNode = $this->phpDocParser->parseType($type);
        $result = TypeHelper::getPossibleTypesByConditionalTypeNode($typeNode);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetPossibleTypesByConditionalTypeNodeData(): array
    {
        return [
            'basic' => [
                '($first is true ? string : string[])',
                ['string', 'string[]'],
            ],
            'with union types' => [
                '($condition is true ? string|int : string)',
                ['string', 'int'],
            ],
            'nested' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>) : static<T>)',
                ['static<T>', 'static<array<string, mixed>>'],
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByArrayTypeNodeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByArrayTypeNode(string $type, array $expectedResult): void
    {
        $typeNode = $this->phpDocParser->parseType($type);
        $result = TypeHelper::getTypesByArrayTypeNode($typeNode);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetTypesByArrayTypeNodeData(): array
    {
        return [
            'basic' => [
                'string[]',
                ['string'],
            ],
            'generic array' => [
                'array<string, mixed>[]',
                ['array<string, mixed>'],
            ],
            'with parentheses' => [
                '(string|int|float)[]',
                ['string', 'int', 'float'],
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByIntersectionTypeNodeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByIntersectionTypeNode(string $type, array $expectedResult): void
    {
        $typeNode = $this->phpDocParser->parseType($type);
        $result = TypeHelper::getTypesByIntersectionTypeNode($typeNode);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetTypesByIntersectionTypeNodeData(): array
    {
        return [
            'basic' => [
                'SomeClass&AnotherClass',
                ['SomeClass', 'AnotherClass'],
            ],
            'with generics' => [
                'SomeClass&AnotherClass<GenericClass>',
                ['SomeClass', 'AnotherClass<GenericClass>'],
            ],
        ];
    }
}
