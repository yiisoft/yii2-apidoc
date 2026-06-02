<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\components;

use Yii;
use yii\apidoc\models\Context;
use yii\helpers\FileHelper;
use yiiunit\apidoc\support\controllers\StubController;
use yiiunit\apidoc\TestCase;

class BaseControllerTest extends TestCase
{
    private StubController $_controller;
    private string $_tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->_controller = new StubController('stub', Yii::$app);
        $this->_controller->interactive = false;
        $this->_tmpDir = sys_get_temp_dir() . '/' . uniqid('apidoc_base_controller_', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->_tmpDir)) {
            FileHelper::removeDirectory($this->_tmpDir);
        }
        parent::tearDown();
    }

    private function tmpPath(string $name): string
    {
        $path = $this->_tmpDir . '/' . $name;
        FileHelper::createDirectory(dirname($path));

        return $path;
    }

    public function testNormalizeTargetDirReturnsExistingDirectoryWhenConfirmed(): void
    {
        $target = $this->tmpPath('existing-dir');
        FileHelper::createDirectory($target);
        $this->_controller->confirmResult = true;

        $this->assertSame($target, $this->invoke($this->_controller, 'normalizeTargetDir', [$target]));
    }

    public function testNormalizeTargetDirAbortsWhenNotConfirmed(): void
    {
        $target = $this->tmpPath('existing-dir-2');
        FileHelper::createDirectory($target);
        $this->_controller->confirmResult = false;

        $this->assertFalse($this->invoke($this->_controller, 'normalizeTargetDir', [$target]));
        $this->assertStringContainsString('User aborted.', $this->_controller->flushStdErrBuffer());
    }

    public function testNormalizeTargetDirFailsWhenTargetIsFile(): void
    {
        $target = $this->tmpPath('a-file.txt');
        file_put_contents($target, 'x');

        $this->assertFalse($this->invoke($this->_controller, 'normalizeTargetDir', [$target]));
        $this->assertStringContainsString('is a file', $this->_controller->flushStdErrBuffer());
    }

    public function testNormalizeTargetDirCreatesMissingDirectory(): void
    {
        $target = $this->tmpPath('created/nested');

        $this->assertSame($target, $this->invoke($this->_controller, 'normalizeTargetDir', [$target]));
        $this->assertDirectoryExists($target);
    }

    public function testLoadContextReturnsEmptyContextWithoutCache(): void
    {
        $location = $this->tmpPath('no-cache');
        FileHelper::createDirectory($location);

        $context = $this->invoke($this->_controller, 'loadContext', [$location]);

        $this->assertInstanceOf(Context::class, $context);
        $this->assertStringContainsString('no data available', $this->_controller->flushStdOutBuffer());
    }

    public function testLoadContextReadsCachedContext(): void
    {
        $location = $this->tmpPath('with-cache');
        FileHelper::createDirectory($location . '/cache');
        $cached = new Context();
        $cached->errors = [['message' => 'cached marker']];
        file_put_contents($location . '/cache/apidoc.data', serialize($cached));

        $context = $this->invoke($this->_controller, 'loadContext', [$location]);

        $this->assertSame([['message' => 'cached marker']], $context->errors);
        $this->assertStringContainsString('done', $this->_controller->flushStdOutBuffer());
    }
}
