<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use yii\apidoc\commands\ApiController;
use yii\apidoc\models\Context;

/**
 * Base class for all API documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class ApiRenderer extends BaseRenderer
{
    /**
     * @var string
     * @see ApiController::$repoUrl
     */
    public $repoUrl;

    /**
     * Renders a given [[Context]].
     *
     * @param Context $context the api documentation context to render.
     * @param $targetDir
     */
    abstract public function render($context, $targetDir);
}
