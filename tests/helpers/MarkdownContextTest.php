<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\Types\Context as PhpDocContext;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\MarkdownContext;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\templates\project\GuideRenderer;

class MarkdownContextTest extends TestCase
{
    public function testPreservesLinksWithoutDocumentationPayload(): void
    {
        $context = new Context();
        $type = new ClassDoc(null, null, ['name' => 'example\\ParentType', 'namespace' => 'example']);
        $type->description = str_repeat('Documentation not needed for links. ', 10000);
        $type->methods['run'] = new MethodDoc($type, null, null, [
            'name' => 'run',
            'definedBy' => $type->name,
            'sourceCode' => str_repeat('// Method source code. ', 10000),
        ]);
        $type->properties['label'] = new PropertyDoc($type, null, null, ['name' => 'label', 'definedBy' => $type->name]);
        $type->constants['VALUE'] = new ConstDoc($type, null, null, ['name' => 'VALUE', 'definedBy' => $type->name]);
        $type->events['EVENT_RUN'] = new EventDoc($type, null, null, ['name' => 'EVENT_RUN', 'definedBy' => $type->name]);
        $context->classes[$type->name] = $type;
        $child = new ClassDoc(null, null, ['name' => 'example\\ChildType', 'parentClass' => $type->name]);
        $child->phpDocContext = new PhpDocContext('example', ['Alias' => $type->name]);
        $child->methods = $type->methods;
        $context->classes[$child->name] = $child;
        $context->interfaces['example\\Contract'] = new InterfaceDoc(null, null, ['name' => 'example\\Contract']);
        $context->traits['example\\ExampleTrait'] = new TraitDoc(null, null, ['name' => 'example\\ExampleTrait']);
        $markdown = '[[example\\ParentType]] [[example\\ChildType::run()]] [[example\\ParentType::$label]] '
            . '[[example\\ParentType::VALUE]] [[example\\ParentType::EVENT_RUN]] '
            . '[[example\\Contract]] [[example\\ExampleTrait]]';
        $originalRenderer = ApiMarkdown::$renderer;

        try {
            $renderer = new GuideRenderer(['apiUrl' => 'api']);
            ApiMarkdown::$renderer = $renderer;
            $renderer->apiContext = $context;
            $expected = ApiMarkdown::process($markdown);
            $expectedContextLinks = ApiMarkdown::process('[[Alias]] [[run()]]', $child->name);
            self::assertStringNotContainsString('broken-link', $expected);
            self::assertStringContainsString('example-parenttype.html#run()-detail', $expected);

            $serialized = serialize(MarkdownContext::create($context));
            self::assertLessThan(strlen(serialize($context)) / 10, strlen($serialized));
            $renderer->apiContext = unserialize($serialized);
            self::assertSame($expected, ApiMarkdown::process($markdown));
            self::assertSame($expectedContextLinks, ApiMarkdown::process('[[Alias]] [[run()]]', $child->name));
            self::assertSame(str_repeat('Documentation not needed for links. ', 10000), $type->description);
        } finally {
            ApiMarkdown::$renderer = $originalRenderer;
        }
    }
}
