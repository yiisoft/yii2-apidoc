<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use Yii;
use yiiunit\apidoc\support\controllers\ApiControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\ApiController
 */
class ApiControllerTest extends TestCase
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
     * @return string command output
     */
    protected function generateApi($sourceDirs, $targetDir = '@runtime')
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', [$sourceDirs, $targetDir]);
    }

    // Tests :

    public function testNoFiles()
    {
        $output = $this->generateApi(Yii::getAlias('@yiiunit/apidoc/data/guide'));

        $this->assertNotEmpty($output);
        $this->assertContains('Error: No files found to process', $output);
    }
}