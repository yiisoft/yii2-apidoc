<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\renderers;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\models\PropertyDoc;
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

    public function testCreateSubjectLinkReturnsPlainNameWhenTypeUnknown(): void
    {
        $parent = new TypeDoc(null, null, ['name' => '\\unknown\\Type']);
        $property = new PropertyDoc($parent, null, null, ['name' => '$id', 'definedBy' => '\\unknown\\Type']);

        $this->assertSame('$id', $this->_renderer->createSubjectLink($property));
    }
}
