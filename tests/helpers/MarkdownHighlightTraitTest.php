<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\ApiMarkdown;

class MarkdownHighlightTraitTest extends TestCase
{
    public function testHighlightEscapesNonPhpCodeKeepingQuotes(): void
    {
        $result = ApiMarkdown::highlight('<b> & "q" \'a\'', 'javascript');

        $this->assertSame('&lt;b&gt; &amp; "q" \'a\'', $result);
    }

    public function testHighlightPhpWithoutOpenTagDropsTagAndWrapper(): void
    {
        $result = ApiMarkdown::highlight('echo 1;', 'php');

        $this->assertStringStartsWith('<span', $result);
        $this->assertStringEndsWith('</span>', $result);
        $this->assertStringContainsString('echo', $result);
        $this->assertStringNotContainsString('&lt;?php', $result);
    }

    public function testHighlightPhpWithOpenTagKeepsTag(): void
    {
        $result = ApiMarkdown::highlight('<?php echo 1;', 'php');

        $this->assertStringStartsWith('<span', $result);
        $this->assertStringEndsWith('</span>', $result);
        $this->assertStringContainsString('&lt;?php', $result);
        $this->assertStringContainsString('echo', $result);
    }
}
