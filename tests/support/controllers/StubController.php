<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support\controllers;

use yii\apidoc\components\BaseController;

/**
 * Concrete {@see BaseController} used to unit test its shared logic.
 */
class StubController extends BaseController
{
    use StdOutBufferControllerTrait;

    public bool $confirmResult = true;

    /**
     * @var string[]
     */
    public array $foundFiles = [];

    public function confirm($message, $default = false)
    {
        return $this->confirmResult;
    }

    protected function findFiles($dir, $except = [])
    {
        return $this->foundFiles;
    }

    protected function findRenderer($template): never
    {
        throw new \BadMethodCallException('findRenderer is not used in these tests.');
    }
}
