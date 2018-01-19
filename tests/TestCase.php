<?php

namespace yiiunit\apidoc;

use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\FileHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->removeRuntimeDirectory();
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'aliases' => [
                '@bower' => '@vendor/bower-asset',
                '@npm' => '@vendor/npm-asset',
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * Asserting two strings equality ignoring line endings
     *
     * @param string $expected
     * @param string $actual
     */
    public function assertEqualsWithoutLE($expected, $actual)
    {
        $expected = str_replace(["\r", "\n"], '', $expected);
        $actual = str_replace(["\r", "\n"], '', $actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Asserts that a haystack contains a needle, ignoring indenting symbols.
     *
     * @param mixed  $needle
     * @param mixed  $haystack
     * @param string $message
     */
    public function assertContainsWithoutIndent($needle, $haystack, $message = '')
    {
        $needle = str_replace(["\r", "\n", "\t", '  '], '', $needle);
        $haystack = str_replace(["\r", "\n", "\t", '  '], '', $haystack);
        $this->assertContains($needle, $haystack, $message);
    }

    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     */
    protected function invoke($object, $method, array $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }

    /**
     * Emulates running of the console controller action.
     * @param \yii\console\Controller|\yiiunit\apidoc\support\controllers\StdOutBufferControllerTrait $controller controller instance.
     * @param string $actionId id of action to be run.
     * @param array $args action arguments.
     * @return string command output.
     * @throws \Throwable on failure.
     */
    protected function runControllerAction($controller, $actionId, array $args = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $controller->run($actionId, $args);
        } catch (\Exception $e) {
            ob_end_flush();
            throw $e;
        } catch (\Throwable $e) {
            ob_end_flush();
            throw $e;
        }
        ob_get_clean();

        return $controller->flushStdErrBuffer() . "\n" . $controller->flushStdOutBuffer();
    }

    /**
     * Removes tests runtime directory as a cleanup.
     */
    protected function removeRuntimeDirectory()
    {
        $runtimePath = Yii::getAlias('@runtime');
        if (empty($runtimePath)) {
            return;
        }
        FileHelper::removeDirectory($runtimePath);
    }
}
