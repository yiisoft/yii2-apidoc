<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\PseudoTypes\Conditional;
use phpDocumentor\Reflection\PseudoTypes\ConditionalForParameter;
use phpDocumentor\Reflection\PseudoTypes\True_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AggregatedType;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\String_;
use yii\apidoc\helpers\TypeHelper;
use yiiunit\apidoc\TestCase;

class TypeHelperTest extends TestCase
{
    /**
     * @dataProvider provideGetTypesByAggregatedTypeData
     *
     * @param Type[] $expectedResult
     */
    public function testGetTypesByAggregatedType(AggregatedType $type, array $expectedResult): void
    {
        $result = TypeHelper::getTypesByAggregatedType($type);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<string, array{AggregatedType, Type[]}>
     */
    public static function provideGetTypesByAggregatedTypeData(): array
    {
        return [
            'compound' => [
                new Compound([new String_(), new Integer(), new Float_()]),
                [new String_(), new Integer(), new Float_()],
            ],
            'intersection' => [
                new Intersection([new Object_(new Fqsen('\\A')), new Object_(new Fqsen('\\B'))]),
                [new Object_(new Fqsen('\\A')), new Object_(new Fqsen('\\B'))],
            ],
        ];
    }

    /**
     * @dataProvider provideGetPossibleTypesByConditionalTypeData
     *
     * @param ConditionalForParameter|Conditional $type
     * @param Type[] $expectedResult
     */
    public function testGetPossibleTypesByConditionalType(Type $type, array $expectedResult): void
    {
        $result = TypeHelper::getPossibleTypesByConditionalType($type);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<string, array{ConditionalForParameter|Conditional, string[]}>
     */
    public static function provideGetPossibleTypesByConditionalTypeData(): array
    {
        return [
            'basic' => [
                new ConditionalForParameter(
                    false,
                    'first',
                    new True_(),
                    new String_(),
                    new Array_(new String_()),
                ),
                [new String_(), new Array_(new String_())],
            ],
            'with compound' => [
                new ConditionalForParameter(
                    false,
                    'first',
                    new True_(),
                    new Compound([new String_(), new Integer()]),
                    new String_(),
                ),
                [new String_(), new Integer()],
            ],
            'nested' => [
                new ConditionalForParameter(
                    false,
                    'value',
                    new True_(),
                    new Conditional(
                        false,
                        new Object_(new Fqsen('\\T')),
                        new Array_(),
                        new Static_(new Object_(new Fqsen('\\T'))),
                        new Static_(new Array_(new Mixed_(), new String_())),
                    ),
                    new Static_(new Object_(new Fqsen('\\T'))),
                ),
                [
                    new Static_(new Object_(new Fqsen('\\T'))),
                    new Static_(new Array_(new Mixed_(), new String_())),
                ],
            ],
        ];
    }
}
