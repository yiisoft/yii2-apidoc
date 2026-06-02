<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\ApiMarkdownLaTeX;

class ApiMarkdownLaTeXTest extends TestCase
{
    #[DataProvider('provideProcessData')]
    public function testProcess(string $markdown, string $expected): void
    {
        $this->assertSame($expected, ApiMarkdownLaTeX::process($markdown));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideProcessData(): array
    {
        return [
            'headline drops inline code' => [
                '# Title `code` here',
                "\\section{Title  here}\n",
            ],
            'plain subsection headline' => [
                '## Just Heading',
                "\\subsection{Just Heading}\n",
            ],
            'code block keeps language' => [
                "```php\n\$x = 1;\n```",
                "\\begin{minted}{php}\n\$x = 1;\n\\end{minted}\n",
            ],
            'code block replaces non-break space' => [
                "```\na\u{00a0}b\n```",
                "\\begin{minted}{text}\na b\n\\end{minted}\n",
            ],
            'inline code' => [
                'use `array_map` here',
                "use \\mintinline{text}{array_map} here\n\n",
            ],
            'inline code flattens newline' => [
                "text `a\nb` end",
                "text \\mintinline{text}{a b} end\n\n",
            ],
            'plain quote' => [
                '> just a quote',
                "\\begin{quote}just a quote\n\n\\end{quote}\n",
            ],
            'note block becomes bold label' => [
                "> Note: be careful\n>\n> second line",
                "\\begin{quote}\\textbf{Note: }be careful\n\nsecond line\n\n\\end{quote}\n",
            ],
            'warning block becomes bold label' => [
                '> Warning: danger',
                "\\begin{quote}\\textbf{Warning: }danger\n\n\\end{quote}\n",
            ],
            'info block matched case insensitively' => [
                '> INFO: details',
                "\\begin{quote}\\textbf{Info: }details\n\n\\end{quote}\n",
            ],
            'tip block becomes bold label' => [
                '> Tip: handy',
                "\\begin{quote}\\textbf{Tip: }handy\n\n\\end{quote}\n",
            ],
            'unknown block type stays plain' => [
                '> Random: not special',
                "\\begin{quote}Random: not special\n\n\\end{quote}\n",
            ],
        ];
    }
}
