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
use ReflectionMethod;
use yii\apidoc\helpers\ApiMarkdown;

class ApiMarkdownLinkTextTest extends TestCase
{
    public function testIncompleteApiLinkRemainsText(): void
    {
        $this->assertSame('<p>[[</p>' . "\n", ApiMarkdown::process('[['));
    }

    public function testPreviousErrorHandlerIsRestored(): void
    {
        $calls = 0;
        set_error_handler(static function () use (&$calls): bool {
            $calls++;

            return true;
        });

        try {
            $markdown = new class () extends ApiMarkdown {
                public function renderLinkText(string $title): string
                {
                    return $this->renderApiLinkText($title);
                }
            };
            $markdown->renderLinkText('A & B');
            trigger_error('after DOM parsing', E_USER_WARNING);
        } finally {
            restore_error_handler();
        }

        $this->assertSame(1, $calls);
    }

    public function testUnexpectedDomWarningIsDelegated(): void
    {
        $receivedMessage = null;
        $previousHandler = static function (int $severity, string $message) use (&$receivedMessage): bool {
            $receivedMessage = $message;

            return true;
        };
        $method = new ReflectionMethod(ApiMarkdown::class, 'handleLoadHtmlWarning');

        $result = $method->invoke(null, $previousHandler, E_WARNING, 'Unexpected warning', __FILE__, __LINE__);

        $this->assertTrue($result);
        $this->assertSame('Unexpected warning', $receivedMessage);
    }

    public function testUnexpectedDomWarningUsesPhpHandlingWithoutPreviousHandler(): void
    {
        $method = new ReflectionMethod(ApiMarkdown::class, 'handleLoadHtmlWarning');

        $result = $method->invoke(null, null, E_WARNING, 'Unexpected warning', __FILE__, __LINE__);

        $this->assertFalse($result);
    }

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
