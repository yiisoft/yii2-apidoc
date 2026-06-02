<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yiiunit\apidoc\support\renderers\StubApiRenderer;
use yiiunit\apidoc\TestCase;

class ApiMarkdownRendererTest extends TestCase
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

    /**
     * @return string[]
     */
    private function errorMessages(): array
    {
        return array_column($this->_renderer->apiContext->errors, 'message');
    }

    public function testGuideLinkIsRewrittenToGeneratedUrl(): void
    {
        $this->assertSame(
            '<p><a href="guide/guide-intro.html">Intro</a></p>' . "\n",
            ApiMarkdown::process('[Intro](guide:intro.md)'),
        );
    }

    public function testEmptyLinkAddsError(): void
    {
        ApiMarkdown::process('[empty]()');

        $this->assertContains('Using empty link.', $this->errorMessages());
    }

    public function testRelativeLinkWithoutRepoUrlAddsWarning(): void
    {
        ApiMarkdown::process('[F](a.md)');

        $this->assertContains('Using relative link (a.md) but repoUrl is not set.', $this->errorMessages());
    }

    public function testRelativeLinkWithRepoUrlIsRewritten(): void
    {
        $this->_renderer->repoUrl = 'https://github.com/x/y';

        $this->assertStringContainsString(
            'href="https://github.com/x/y/src/a.php"',
            ApiMarkdown::process('[F](src/a.php)'),
        );
        $this->assertSame([], $this->_renderer->apiContext->errors);
    }

    public function testInlineLinkTagWithDescriptionBecomesLink(): void
    {
        $this->assertSame(
            '<p>x <a href="https://example.com">Example</a></p>' . "\n",
            ApiMarkdown::process('x {@link https://example.com Example}'),
        );
    }

    public function testInlineLinkTagWithoutDescriptionUsesUrlAsText(): void
    {
        $this->assertStringContainsString(
            '<a href="https://example.com">https://example.com</a>',
            ApiMarkdown::process('{@link https://example.com}'),
        );
    }

    public function testInvalidInlineLinkTagAddsErrorAndFallsBackToDescription(): void
    {
        $result = ApiMarkdown::process('t {@link foo bar}');

        $this->assertSame('<p>t bar</p>' . "\n", $result);
        $this->assertContains('Invalid inline tag "@link": {@link foo bar}', $this->errorMessages());
    }

    public function testInlineSeeTagLinksToPhpFunction(): void
    {
        $this->assertStringContainsString(
            'href="https://www.php.net/manual/en/function.strlen.php"',
            ApiMarkdown::process('{@see strlen()}'),
        );
    }
}
