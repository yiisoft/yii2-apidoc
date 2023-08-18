<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support\controllers;

use yii\apidoc\commands\GuideController;

/**
 * {@inheritdoc}
 */
class GuideControllerMock extends GuideController
{
    use StdOutBufferControllerTrait;
}