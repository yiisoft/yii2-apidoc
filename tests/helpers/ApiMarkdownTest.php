<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\ApiMarkdown;

class ApiMarkdownTest extends TestCase
{
    #[DataProvider('provideProcessData')]
    public function testProcess(?string $markdown, string $expected): void
    {
        $this->assertSame($expected, ApiMarkdown::process($markdown));
    }

    /**
     * @return array<string, array{string|null, string}>
     */
    public static function provideProcessData(): array
    {
        return [
            'null content' => [null, ''],
            'empty content' => ['', ''],
            'single headline has no toc' => [
                '# Only One',
                '<h1>Only One <span id="only-one"></span><a href="#only-one" class="hashlink">&para;</a></h1>',
            ],
            'multiple headlines build toc' => [
                "## First\n\nText\n\n## Second\n\nMore",
                '<div class="toc"><ol><li><a href="#first">First</a></li>' . "\n"
                    . '<li><a href="#second">Second</a></li></ol></div>' . "\n"
                    . '<h2>First <span id="first"></span><a href="#first" class="hashlink">&para;</a></h2><p>Text</p>' . "\n"
                    . '<h2>Second <span id="second"></span><a href="#second" class="hashlink">&para;</a></h2>'
                    . '<p>More</p>' . "\n",
            ],
            'table gets bootstrap classes' => [
                "| A | B |\n|---|---|\n| 1 | 2 |",
                '<table class="table table-bordered table-striped">' . "\n"
                    . '<thead>' . "\n"
                    . '<tr><th>A </th><th>B</th></tr>' . "\n"
                    . '</thead>' . "\n"
                    . '<tbody>' . "\n"
                    . '<tr><td>1 </td><td>2</td></tr>' . "\n"
                    . '</tbody>' . "\n"
                    . '</table>' . "\n",
            ],
            'note blockquote gets class and bold label' => [
                '> Note: careful',
                '<blockquote class="note"><p><strong>Note: </strong>careful</p>' . "\n" . '</blockquote>' . "\n",
            ],
            'warning blockquote gets class and bold label' => [
                '> Warning: x',
                '<blockquote class="warning"><p><strong>Warning: </strong>x</p>' . "\n" . '</blockquote>' . "\n",
            ],
            'unknown blockquote type stays plain' => [
                '> Foo: x',
                '<blockquote><p>Foo: x</p>' . "\n" . '</blockquote>' . "\n",
            ],
        ];
    }

    public function testProcessParagraphMode(): void
    {
        $this->assertSame('hello', ApiMarkdown::process('hello', null, true));
    }

    #[DataProvider('provideCodeBlockData')]
    public function testCodeBlockHighlighting(string $markdown, string $expectedClass): void
    {
        $this->assertStringContainsString($expectedClass, ApiMarkdown::process($markdown));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideCodeBlockData(): array
    {
        return [
            'php is highlighted with language class' => [
                "```php\necho 1;\n```",
                'class="hljs php language-php"',
            ],
            'json is highlighted with language class' => [
                "```json\n{\"a\":1}\n```",
                'class="hljs json language-json"',
            ],
            'php with short echo tag falls back to html' => [
                "```php\n<?= \$x ?>\n```",
                'language-html',
            ],
        ];
    }

    public function testCodeBlockWithoutLanguageIsAutoDetected(): void
    {
        $result = ApiMarkdown::process("```\nSELECT * FROM t\n```");

        $this->assertStringContainsString('class="hljs', $result);
        $this->assertStringNotContainsString('language-', $result);
    }

    public function testGetHeadingsCollectsLevelTwoHeadings(): void
    {
        $markdown = new ApiMarkdown();
        $markdown->parse("## A\n\n## B");

        $this->assertSame([
            ['title' => 'A', 'id' => 'a'],
            ['title' => 'B', 'id' => 'b'],
        ], $markdown->getHeadings());
    }

    public function testGetHeadingsNestsDeeperHeadingsUnderLevelTwo(): void
    {
        $markdown = new ApiMarkdown();
        $markdown->parse("## Parent\n\n### Child\n\n### Child2");

        $this->assertSame([
            [
                'title' => 'Parent',
                'id' => 'parent',
                'sub' => [
                    ['title' => 'Child', 'id' => 'child'],
                    ['title' => 'Child2', 'id' => 'child2'],
                ],
            ],
        ], $markdown->getHeadings());
    }
}
