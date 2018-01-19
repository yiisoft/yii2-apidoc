<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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
    protected function setUp()
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
     * @return string command output
     */
    protected function generateGuide($sourceDirs, $targetDir = '@runtime')
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', [$sourceDirs, $targetDir]);
    }

    // Tests :

    public function testNoFiles()
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/support'));

        $this->assertNotEmpty($output);
        $this->assertContains('Error: No files found to process', $output);
    }

    public function testGenerate()
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/data/guide'));

        $this->assertNotEmpty($output);
        $this->assertContains('generating search index...done.', $output);
        $this->assertContains('Publishing images...done.', $output);

        $readmeFile = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'guide-README.html';
        $this->assertTrue(file_exists($readmeFile));
        $readmeContent = file_get_contents($readmeFile);
        $this->assertContains('<h1>The Test Guide <span id="the-test-guide"></span><a href="#the-test-guide" class="hashlink">', $readmeContent);
        $this->assertContains('<a href="guide-intro.html">Intro</a>', $readmeContent);
        $this->assertContains('<a href="guide-intro-upgrade.html">Upgrade</a>', $readmeContent);
    }
}