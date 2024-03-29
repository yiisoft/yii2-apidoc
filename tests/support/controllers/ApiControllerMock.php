<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support\controllers;

use yii\apidoc\commands\ApiController;

/**
 * {@inheritdoc}
 */
class ApiControllerMock extends ApiController
{
    use StdOutBufferControllerTrait;
}
