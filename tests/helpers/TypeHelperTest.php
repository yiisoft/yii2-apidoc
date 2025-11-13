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
            'conditional with parentheses' => [
                '($first is true ? string[] : string)',
                true,
            ],
            'conditional without parentheses' => [
                '$first is true ? string[] : string',
                false,
            ],
            'nested conditionals' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>) : static<T>)',
                true,
            ],
            'incomplete conditional' => [
                '($value is true ? (T is array ? static<T> : static<array<string, mixed>>))',
                false,
            ],
            'conditional with invalid parentheses' => [
                '($first is true ? string[] : string',
                false,
            ],
            'scalar' => [
                'string',
                false,
            ],
            'array' => [
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
     * @dataProvider provideIsGenericTypeData
     */
    public function testIsGenericType(string $string, bool $expectedResult): void
    {
        $result = $this->typeHelper->isGenericType($string);
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
            'empty string' => [
                '',
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
        $result = $this->typeHelper->getTypesByGenericType($string);
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
            'generic array' => [
                'array<string, mixed>',
                ['array<string, mixed>'],
            ],
            'union' => [
                'string|int|float',
                ['string', 'int', 'float'],
            ],
        ];
    }
}
