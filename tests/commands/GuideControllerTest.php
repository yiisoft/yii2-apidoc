<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use Spatie\Snapshots\MatchesSnapshots;
use Yii;
use yiiunit\apidoc\support\controllers\GuideControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\GuideController
 */
class GuideControllerTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Creates test controller instance.
     * @return GuideControllerMock
     */
    protected function createController()
    {
        $controller = new GuideControllerMock('guide', Yii::$app);
        $controller->interactive = false;

        return $controller;
    }

    /**
     * @param string $sourceDirs
     * @param string $targetDir
     * @param array $args
     * @return string command output
     */
    protected function generateGuide($sourceDirs, $targetDir = '@runtime', array $args = [])
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', array_merge([$sourceDirs, $targetDir], $args));
    }

    // Tests :

    public function testNoFiles(): void
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/support'));

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Error: No files found to process', $output);
    }

    /**
     * @dataProvider provideGenerateHtmlData
     */
    public function testGenerateHtml(string $template): void
    {
        $output = $this->generateGuide(
            Yii::getAlias('@yiiunit/apidoc/data/guide'),
            '@runtime',
            ['template' => $template]
        );

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('generating search index...done.', $output);
        $this->assertStringContainsString('Publishing images...done.', $output);

        $outputPath = Yii::getAlias('@runtime');

        foreach (['README', 'intro', 'TOC'] as $filename) {
            $filePath = $outputPath . DIRECTORY_SEPARATOR . "guide-{$filename}.html";
            $this->assertTrue(file_exists($filePath));
            $fileContent = $this->removeDynamicContentFromHtml(file_get_contents($filePath));
            $this->assertMatchesHtmlSnapshot($fileContent);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideGenerateHtmlData(): array
    {
        return [
            'bootstrap' => ['bootstrap'],
            'project' => ['project'],
        ];
    }

    public function testGeneratePdf(): void
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/data/guide'), '@runtime', ['template' => 'pdf']);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Publishing images...done.', $output);

        $outputPath = Yii::getAlias('@runtime');
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'Makefile'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'main.tex'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'guide.tex'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'title.tex'));
    }
}
