<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\renderers;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\String_;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\PseudoTypeDoc;
use yii\apidoc\models\PseudoTypeImportDoc;
use yii\apidoc\models\TypeDoc;
use yiiunit\apidoc\support\renderers\StubApiRenderer;
use yiiunit\apidoc\TestCase;

class BaseRendererTest extends TestCase
{
    private StubApiRenderer $_renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_renderer = new StubApiRenderer();
    }

    protected function tearDown(): void
    {
        ApiMarkdown::$renderer = null;
        ApiMarkdownLaTeX::$renderer = null;
        parent::tearDown();
    }

    public function testCreateSubjectLinkForMethodResolvedByType(): void
    {
        $type = new TypeDoc(null, null, ['name' => '\\app\\Post']);
        $this->_renderer->apiContext->classes['app\\Post'] = $type;
        $method = new MethodDoc($type, null, null, ['name' => 'save', 'definedBy' => '\\app\\Post']);

        $this->assertSame(
            '<a href="app-Post.html#save()-detail">save()</a>',
            $this->_renderer->createSubjectLink($method),
        );
    }

    public function testCreateSubjectLinkReturnsPlainNameWhenTypeUnknown(): void
    {
        $parent = new TypeDoc(null, null, ['name' => '\\unknown\\Type']);
        $property = new PropertyDoc($parent, null, null, ['name' => '$id', 'definedBy' => '\\unknown\\Type']);

        $this->assertSame('$id', $this->_renderer->createSubjectLink($property));
    }

    public function testCreateSubjectLinkForPseudoType(): void
    {
        $parent = new TypeDoc(null, null, ['name' => '\\app\\Post']);
        $pseudoType = new PseudoTypeDoc(PseudoTypeDoc::TYPE_PHPSTAN, $parent, 'MyType', new String_());

        $this->assertSame(
            '<a href="app-Post.html#phpstan-type-MyType">MyType</a>',
            $this->_renderer->createSubjectLink($pseudoType),
        );
    }

    public function testCreateSubjectLinkForPseudoTypeImport(): void
    {
        $import = new PseudoTypeImportDoc(PseudoTypeImportDoc::TYPE_PSALM, 'ImportedType', new Fqsen('\\app\\Source'));

        $this->assertSame(
            '<a href="app-Source.html#psalm-type-ImportedType">ImportedType</a>',
            $this->_renderer->createSubjectLink($import),
        );
    }

    public function testGenerateGuideUrlReturnsExternalUrlUnchanged(): void
    {
        $this->assertSame('https://x.com/page', $this->_renderer->generateGuideUrl('https://x.com/page'));
    }

    public function testGenerateGuideUrlKeepsHashFragment(): void
    {
        $this->_renderer->guideUrl = 'docs';

        $this->assertSame('docs/guide-intro.html#sec', $this->_renderer->generateGuideUrl('intro.md#sec'));
    }

    public function testGenerateGuideUrlForPlainFile(): void
    {
        $this->_renderer->guideUrl = 'docs';

        $this->assertSame('docs/guide-start.html', $this->_renderer->generateGuideUrl('guide/start.md'));
    }
}
