<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use InvalidArgumentException;
use yii\apidoc\helpers\TypeHelper;
use yiiunit\apidoc\TestCase;

class TypeHelperTest extends TestCase
{
    private TypeHelper $typeHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeHelper = new TypeHelper();
    }

    /**
     * @dataProvider provideIsConditionalTypeData
     */
    public function testIsConditionalType(string $string, bool $expectedResult): void
    {
        $result = $this->typeHelper->isConditionalType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideIsConditionalTypeData(): array
    {
        return [
            'conditional type with parentheses' => [
                '($first is true ? string[] : string)',
                true,
            ],
            'conditional type without parentheses' => [
                '$first is true ? string[] : string',
                false,
            ],
            'nested conditional types' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>) : static<T>)',
                true,
            ],
            'incomplete conditional type' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>))',
                false,
            ],
            'conditional type with invalid parentheses' => [
                '($first is true ? string[] : string',
                false,
            ],
            'scalar type' => [
                'string',
                false,
            ],
            'array type' => [
                'array<string, mixed>',
                false,
            ],
            'empty string' => [
                '',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetGenericTypesData
     *
     * @param string[] $expectedResult
     */
    public function testGetGenericTypes(string $string, array $expectedResult): void
    {
        $result = $this->typeHelper->getGenericTypes($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}
     */
    public static function provideGetGenericTypesData(): array
    {
        return [
            'array with generics' => [
                'array<string, mixed>',
                ['string', 'mixed'],
            ],
            'array without generics' => [
                'array',
                [],
            ],
            'some class with generics' => [
                'Action<Controller>',
                ['Controller'],
            ],
            'some class without generics' => [
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
        $result = $this->typeHelper->getPossibleTypesByConditionalType($string);
        $this->assertSame($expectedResult, $result);
    }

    public function testGetPossibleTypesByConditionalTypeWithInvalidType(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Type (int) is not conditional'));
        $this->typeHelper->getPossibleTypesByConditionalType('int');
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetPossibleTypesByConditionalTypeData(): array
    {
        return [
            'basic' => [
                '($first is true ? string[] : string)',
                ['string[]', 'string'],
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
        $result = $this->typeHelper->getTypesByArrayType($string);
        $this->assertSame($expectedResult, $result);
    }

    public function testGetTypesByArrayTypeWithInvalidType(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Type (int) is not array'));
        $this->typeHelper->getTypesByArrayType('int');
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
            'arrays with generics' => [
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
     * @dataProvider provideGetChildTypesByTypeData
     *
     * @param string[] $expectedResult
     */
    public function testGetChildTypesByType(string $string, array $expectedResult): void
    {
        $result = $this->typeHelper->getChildTypesByType($string);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function provideGetChildTypesByTypeData(): array
    {
        return [
            'scalar' => [
                'string',
                ['string'],
            ],
            'array with generics' => [
                'array<string, mixed>',
                ['array<string, mixed>'],
            ],
            'union type' => [
                'string|int|float',
                ['string', 'int', 'float'],
            ],
        ];
    }
}
