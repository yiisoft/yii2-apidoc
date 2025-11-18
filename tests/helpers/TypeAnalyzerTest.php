<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use InvalidArgumentException;
use yii\apidoc\helpers\TypeAnalyzer;
use yiiunit\apidoc\TestCase;

class TypeAnalyzerTest extends TestCase
{
    private TypeAnalyzer $typeAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeAnalyzer = new TypeAnalyzer();
    }

    /**
     * @dataProvider provideIsConditionalTypeData
     */
    public function testIsConditionalType(string $string, bool $expectedResult): void
    {
        $result = $this->typeAnalyzer->isConditionalType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideIsConditionalTypeData(): array
    {
        return [
            'conditional with parentheses' => [
                '($first is true ? string : string[])',
                true,
            ],
            'nested conditionals' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>) : static<T>)',
                true,
            ],
            'scalar' => [
                'string',
                false,
            ],
            'array' => [
                'array<string, mixed>',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideIsGenericTypeData
     */
    public function testIsGenericType(string $string, bool $expectedResult): void
    {
        $result = $this->typeAnalyzer->isGenericType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideIsGenericTypeData(): array
    {
        return [
            'generic array' => [
                'array<string, mixed>',
                true,
            ],
            'scalar' => [
                'string',
                false,
            ],
            'basic array' => [
                'array',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByGenericTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByGenericType(string $string, array $expectedResult): void
    {
        $result = $this->typeAnalyzer->getTypesByGenericType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}
     */
    public static function provideGetTypesByGenericTypeData(): array
    {
        return [
            'generic array' => [
                'array<string, mixed>',
                ['string', 'mixed'],
            ],
            'basic array' => [
                'array',
                [],
            ],
            'generic class' => [
                'Action<Controller>',
                ['Controller'],
            ],
            'basic class' => [
                'Action',
                [],
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
     * @dataProvider provideGetPossibleTypesByConditionalTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetPossibleTypesByConditionalType(string $string, array $expectedResult): void
    {
        $result = $this->typeAnalyzer->getPossibleTypesByConditionalType($string);
        $this->assertSame($expectedResult, $result);
    }

    public function testGetPossibleTypesByConditionalTypeWithInt(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Type (int) is not conditional'));
        $this->typeAnalyzer->getPossibleTypesByConditionalType('int');
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetPossibleTypesByConditionalTypeData(): array
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
     * @dataProvider provideGetTypesByArrayTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByArrayType(string $string, array $expectedResult): void
    {
        $result = $this->typeAnalyzer->getTypesByArrayType($string);
        $this->assertSame($expectedResult, $result);
    }

    public function testGetTypesByArrayTypeWithInt(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Type (int) is not array'));
        $this->typeAnalyzer->getTypesByArrayType('int');
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetTypesByArrayTypeData(): array
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
     * @dataProvider provideIsUnionTypeData
     *
     * @param string[] $expectedResult
     */
    public function testIsUnionType(string $string, bool $expectedResult): void
    {
        $result = $this->typeAnalyzer->isUnionType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideIsUnionTypeData(): array
    {
        return [
            'union' => [
                'string|int',
                true,
            ],
            'intersection' => [
                '\Exception&\SomeException',
                false,
            ],
            'scalar' => [
                'string',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByUnionTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByUnionType(string $string, array $expectedResult): void
    {
        $result = $this->typeAnalyzer->getTypesByUnionType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetTypesByUnionTypeData(): array
    {
        return [
            'scalars' => [
                'string|int|float',
                ['string', 'int', 'float'],
            ],
            'generics' => [
                'array<string, mixed>|Action<Controller>',
                ['array<string, mixed>', 'Action<Controller>'],
            ],
        ];
    }

    public function testGetTypesByUnionTypeWithString(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Type (string) is not union'));
        $this->typeAnalyzer->getTypesByUnionType('string');
    }

    /**
     * @dataProvider provideIsIntersectionTypeData
     */
    public function testIsIntersectionType(string $string, bool $expectedResult): void
    {
        $result = $this->typeAnalyzer->isIntersectionType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideIsIntersectionTypeData(): array
    {
        return [
            'intersection' => [
                'SomeClass&AnotherClass',
                true,
            ],
            'generic array' => [
                'array<string, mixed>',
                false,
            ],
            'scalar' => [
                'string',
                false,
            ],
            'basic array' => [
                'array',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetTypesByIntersectionTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetTypesByIntersectionType(string $string, array $expectedResult): void
    {
        $result = $this->typeAnalyzer->getTypesByIntersectionType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetTypesByIntersectionTypeData(): array
    {
        return [
            'intersection' => [
                'SomeClass&AnotherClass',
                ['SomeClass', 'AnotherClass'],
            ],
            'union' => [
                'int|string',
                [],
            ],
            'array' => [
                'array',
                [],
            ],
            'scalar' => [
                'string',
                [],
            ],
        ];
    }
}
