<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use yii\apidoc\helpers\PhpDocTagFactory;
use yii\apidoc\helpers\PhpDocTagParser;
use yiiunit\apidoc\TestCase;

class PhpDocTagFactoryTest extends TestCase
{
    private PhpDocTagFactory $phpDocTagFactory;

    private PhpDocTagParser $phpDocTagParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpDocTagFactory = new PhpDocTagFactory();
        $this->phpDocTagParser = new PhpDocTagParser();
    }

    public function testCreateTagWithTypesByTagNode(): void
    {
        $paramTagNode = $this->phpDocTagParser->parseTag('@param key-of<self::COLORS> $colorKey some param description');

        /** @var Param|Tag */
        $paramTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($paramTagNode);
        $this->assertInstanceOf(Param::class, $paramTag);
        $this->assertSame('colorKey', $paramTag->getVariableName());
        $this->assertSame('key-of<self::COLORS>', (string) $paramTag->getType());
        $this->assertSame('some param description', (string) $paramTag->getDescription());

        $returnTagNode = $this->phpDocTagParser->parseTag('@return value-of<self::COLORS> some return description');

        /** @var Return_|Tag */
        $returnTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($returnTagNode);
        $this->assertInstanceOf(Return_::class, $returnTag);
        $this->assertSame('value-of<self::COLORS>', (string) $returnTag->getType());
        $this->assertSame('some return description', (string) $returnTag->getDescription());

        $varTagNode = $this->phpDocTagParser->parseTag('@var int-mask<1, 2, 4> $intMaskVar some var description');

        /** @var Var_|Tag */
        $varTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($varTagNode);
        $this->assertInstanceOf(Var_::class, $varTag);
        $this->assertSame('intMaskVar', $varTag->getVariableName());
        $this->assertSame('int-mask<1, 2, 4>', (string) $varTag->getType());
        $this->assertSame('some var description', (string) $varTag->getDescription());

        $propertyTagNode = $this->phpDocTagParser->parseTag(
            '@property int-mask-of<1|2|4> $intMaskProperty some property description'
        );

        /** @var Property|Tag */
        $propertyTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($propertyTagNode);
        $this->assertInstanceOf(Property::class, $propertyTag);
        $this->assertSame('intMaskProperty', $propertyTag->getVariableName());
        $this->assertSame('int-mask-of<(1 | 2 | 4)>', (string) $propertyTag->getType());
        $this->assertSame('some property description', (string) $propertyTag->getDescription());

        $propertyReadTagNode = $this->phpDocTagParser->parseTag(
            '@property-read (int|string)[] $arrayReadProperty some property-read description'
        );

        /** @var PropertyRead|Tag */
        $propertyReadTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($propertyReadTagNode);
        $this->assertInstanceOf(PropertyRead::class, $propertyReadTag);
        $this->assertSame('arrayReadProperty', $propertyReadTag->getVariableName());
        $this->assertSame('(int | string)[]', (string) $propertyReadTag->getType());
        $this->assertSame('some property-read description', (string) $propertyReadTag->getDescription());

        $propertyWriteTagNode = $this->phpDocTagParser->parseTag(
            '@property-write array<string, mixed> $arrayWriteProperty some property-write description'
        );

        /** @var PropertyWrite|Tag */
        $propertyWriteTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($propertyWriteTagNode);
        $this->assertInstanceOf(PropertyWrite::class, $propertyWriteTag);
        $this->assertSame('arrayWriteProperty', $propertyWriteTag->getVariableName());
        $this->assertSame('array<string, mixed>', (string) $propertyWriteTag->getType());
        $this->assertSame('some property-write description', (string) $propertyWriteTag->getDescription());

        $methodTagNode = $this->phpDocTagParser->parseTag(
            '@method array<string, string> someMethod(bool $param1, array $param2) some method description'
        );

        /** @var Method|Tag */
        $methodTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($methodTagNode);
        $this->assertInstanceOf(Method::class, $methodTag);
        $this->assertSame('someMethod', $methodTag->getMethodName());
        $this->assertSame('array<string, string>', (string) $methodTag->getReturnType());
        $this->assertSame('some method description', (string) $methodTag->getDescription());
        $this->assertSame(
            [['param1', 'bool'], ['param2', 'array']],
            array_map(
                fn(MethodParameter $methodParameter) => [$methodParameter->getName(), (string) $methodParameter->getType()],
                $methodTag->getParameters()
            )
        );

        $templateTagNode = $this->phpDocTagParser->parseTag(
            '@template T of Action<Controller> some template description'
        );

        /** @var Template|Tag */
        $templateTag = $this->phpDocTagFactory->createTagWithTypesByTagNode($templateTagNode);
        $this->assertInstanceOf(Template::class, $templateTag);
        $this->assertSame('T', $templateTag->getTemplateName());
        $this->assertSame('Action<Controller>', (string) $templateTag->getBound());
        $this->assertNull($templateTag->getDefault());
        $this->assertSame('some template description', (string) $templateTag->getDescription());
    }
}
