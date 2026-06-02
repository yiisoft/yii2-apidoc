<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use cebe\jssearch\tokenizer\StandardTokenizer;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\apidoc\helpers\ApiIndexer;
use yiiunit\apidoc\TestCase;

class ApiIndexerTest extends TestCase
{
    #[DataProvider('provideGenerateFileInfoTitleAndDescriptionData')]
    public function testGenerateFileInfoTitleAndDescription(
        string $contents,
        string $expectedTitle,
        string $expectedDescription
    ): void {
        $indexer = new ApiIndexer();
        $result = $this->invoke($indexer, 'generateFileInfo', [
            '/base/path/sub/file.html',
            $contents,
            '/base/path',
            'https://example.com',
        ]);

        $this->assertSame($expectedTitle, $result['t']);
        $this->assertSame($expectedDescription, $result['d']);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function provideGenerateFileInfoTitleAndDescriptionData(): array
    {
        return [
            'h1 strips para entity and tags' => [
                '<h1>The <code>Title</code>&para;</h1>',
                'The Title',
                '',
            ],
            'title tag used when no h1' => [
                '<title>Page Title</title><p>ignored when class description present</p>',
                'Page Title',
                'ignored when class description present',
            ],
            'no title marker when neither h1 nor title' => [
                '<div>no headings here</div>',
                '<i>No title</i>',
                '',
            ],
            'class description has priority over paragraph' => [
                '<h1>Class</h1><div id="classDescription">'
                    . "\n" . '<strong>Class summary text</strong></div><p>Paragraph</p>',
                'Class',
                'Class summary text',
            ],
            'paragraph used when no class description' => [
                '<h1>Guide</h1><p>First <em>paragraph</em> text</p>',
                'Guide',
                'First paragraph text',
            ],
            'h1 spanning multiple lines is matched' => [
                "<h1>Multi\nLine</h1>",
                "Multi\nLine",
                '',
            ],
        ];
    }

    #[DataProvider('provideGenerateFileInfoUrlData')]
    public function testGenerateFileInfoUrl(
        string $file,
        string $basePath,
        string $baseUrl,
        string $expectedUrl
    ): void {
        $indexer = new ApiIndexer();
        $result = $this->invoke($indexer, 'generateFileInfo', [
            $file,
            '<h1>Title</h1>',
            $basePath,
            $baseUrl,
        ]);

        $this->assertSame($expectedUrl, $result['u']);
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function provideGenerateFileInfoUrlData(): array
    {
        return [
            'strips base path and keeps relative part' => [
                '/base/path/sub/file.html',
                '/base/path',
                'https://example.com',
                'https://example.com/sub/file.html',
            ],
            'trailing slash in base path is trimmed' => [
                '/base/path/file.html',
                '/base/path/',
                'https://example.com',
                'https://example.com/file.html',
            ],
            'backslashes are normalized to forward slashes' => [
                '\\base\\path\\sub\\file.html',
                '\\base\\path',
                'https://example.com',
                'https://example.com/sub/file.html',
            ],
        ];
    }

    public function testGetTokenizerAddsYiiStopWordOnce(): void
    {
        $indexer = new ApiIndexer();

        $tokenizer = $indexer->getTokenizer();
        $this->assertInstanceOf(StandardTokenizer::class, $tokenizer);
        $this->assertContains('yii', $tokenizer->stopWords);

        $tokenizerAgain = $indexer->getTokenizer();
        $this->assertInstanceOf(StandardTokenizer::class, $tokenizerAgain);
        $this->assertContains('yii', $tokenizerAgain->stopWords);
        $occurrences = array_filter($tokenizerAgain->stopWords, static fn($word) => $word === 'yii');
        $this->assertCount(1, $occurrences);
    }
}
