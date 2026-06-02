<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\IndexFileAnalyzer;

class IndexFileAnalyzerTest extends TestCase
{
    public function testAnalyzeBuildsChaptersFromToc(): void
    {
        $text = <<<MARKDOWN
        The Test Guide
        ==============

        This guide serves the unit testing purposes.

        Introduction
        ------------

        * [Intro](intro.md)
        * [Upgrade](intro-upgrade.md)

        Advanced
        --------

        * [Caching](caching.md)
        MARKDOWN;

        $analyzer = new IndexFileAnalyzer();
        $chapters = $analyzer->analyze($text);

        $this->assertSame('The Test Guide', $analyzer->title);
        $this->assertSame('', $analyzer->introduction);
        $this->assertSame([
            2 => [
                'headline' => 'Introduction',
                'content' => [
                    ['headline' => 'Intro', 'file' => 'intro.md'],
                    ['headline' => 'Upgrade', 'file' => 'intro-upgrade.md'],
                ],
            ],
            3 => [
                'headline' => 'Advanced',
                'content' => [
                    ['headline' => 'Caching', 'file' => 'caching.md'],
                ],
            ],
        ], $chapters);
    }

    public function testIntroductionCollectsParagraphsBeforeFirstHeadline(): void
    {
        $text = <<<MARKDOWN
        First introduction line.

        Second introduction line.
        MARKDOWN;

        $analyzer = new IndexFileAnalyzer();
        $chapters = $analyzer->analyze($text);

        $this->assertSame([], $chapters);
        $this->assertStringContainsString('First introduction line.', (string) $analyzer->introduction);
        $this->assertStringContainsString('Second introduction line.', (string) $analyzer->introduction);
    }
}
