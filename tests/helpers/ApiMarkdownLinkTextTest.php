<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use ErrorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\ApiMarkdown;

class ApiMarkdownLinkTextTest extends TestCase
{
    #[DataProvider('provideMalformedTitleData')]
    public function testMalformedHtmlDoesNotRaiseWarnings(string $title, ?string $expected): void
    {
        $markdown = new class () extends ApiMarkdown {
            public function renderLinkText(string $title): string
            {
                return $this->renderApiLinkText($title);
            }
        };

        set_error_handler(
            static fn (int $severity, string $message): bool => throw new ErrorException($message, 0, $severity),
        );

        try {
            $result = $markdown->renderLinkText($title);
            if ($expected === null) {
                $this->assertTrue($result === '' || str_contains($result, '$value'));
            } else {
                $this->assertSame($expected, $result);
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function provideMalformedTitleData(): array
    {
        return [
            'empty title' => ['', ''],
            'whitespace' => [' ', ''],
            'empty paragraph' => ['<p></p>', ''],
            // libxml preserves processing instructions differently across platforms.
            'PHP processing instruction' => ['<?php echo $value; ?>', null],
            'unescaped ampersand' => ['A & B', 'A &amp; B'],
        ];
    }
}
