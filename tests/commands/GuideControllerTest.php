<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use Yii;
use yiiunit\apidoc\support\controllers\GuideControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\GuideController
 */
class GuideControllerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    // Tests :

    public function testNoFiles()
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/support'));

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Error: No files found to process', $output);
    }

    public function testGenerateBootstrap()
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/data/guide'), '@runtime', ['template' => 'bootstrap']);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('generating search index...done.', $output);
        $this->assertStringContainsString('Publishing images...done.', $output);

        $outputPath = Yii::getAlias('@runtime');

        $readmeFile = $outputPath . DIRECTORY_SEPARATOR . 'guide-README.html';
        $this->assertTrue(file_exists($readmeFile));
        $readmeContent = file_get_contents($readmeFile);
        $this->assertStringContainsString('<h1>The Test Guide <span id="the-test-guide"></span><a href="#the-test-guide" class="hashlink">', $readmeContent);
        $this->assertStringContainsString('<a href="guide-intro.html">Intro</a>', $readmeContent);
        $this->assertStringContainsString('<a href="guide-intro-upgrade.html">Upgrade</a>', $readmeContent);

        $tocFile = $outputPath . DIRECTORY_SEPARATOR . 'guide-TOC.html';
        $this->assertTrue(file_exists($tocFile));
        $tocFile = file_get_contents($tocFile);

        $this->assertStringContainsString('<h1>TOC Test <span id="toc-test"></span><a href="#toc-test" class="hashlink">', $tocFile);
        $this->assertEquals(1, substr_count($tocFile, '<div class="toc">'));
    }

    public function testGeneratePdf()
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
}
