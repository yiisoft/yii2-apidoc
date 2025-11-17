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
use yii\apidoc\helpers\TypeHelper;
use yiiunit\apidoc\TestCase;

class TypeHelperTest extends TestCase
{
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
                ['string', 'int'],
            ],
        ];
    }
}
