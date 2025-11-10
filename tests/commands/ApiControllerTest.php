<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use Spatie\Snapshots\MatchesSnapshots;
use Yii;
use yii\helpers\FileHelper;
use yiiunit\apidoc\support\controllers\ApiControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\ApiController
 */
class ApiControllerTest extends TestCase
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
     * @return ApiControllerMock
     */
    protected function createController()
    {
        $controller = new ApiControllerMock('api', Yii::$app);
        $controller->interactive = false;

        return $controller;
    }

    /**
     * @param string $sourceDirs
     * @param string $targetDir
     * @param array $args
     * @return string command output
     */
    protected function generateApi($sourceDirs, $targetDir = '@runtime', array $args = [])
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', array_merge([$sourceDirs, $targetDir], $args));
    }

    // Tests :

    public function testNoFiles()
    {
        $output = $this->generateApi(Yii::getAlias('@yiiunit/apidoc/data/guide'));

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Error: No files found to process', $output);
    }

    public function testGenerateBootstrap()
    {
        $sourceFilesDir = Yii::getAlias('@yiiunit/apidoc/data/api');
        $output = $this->generateApi($sourceFilesDir, '@runtime', ['template' => 'bootstrap']);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('generating search index...done.', $output);

        $filesCount = 0;
        $outputPath = Yii::getAlias('@runtime');

        foreach (glob("{$outputPath}/yiiunit-apidoc-data-api*") as $filePath) {
            $fileContent = file_get_contents($filePath);

            // Deleting dynamic content
            $fileContent = preg_replace('/<p\s+class="pull-right">.*?<\/p>/is', '', $fileContent);
            $fileContent = preg_replace('/<script\s+src=".*?"><\/script>/is', '', $fileContent);
            $fileContent = preg_replace('/<link\s+href=".*?" rel="stylesheet">/is', '', $fileContent);

            $this->assertMatchesHtmlSnapshot($fileContent);
            $filesCount++;
        }

        $sourceFilesCount = count(FileHelper::findFiles($sourceFilesDir, ['recursive' => true]));

        $this->assertSame($sourceFilesCount, $filesCount);
    }
}
