<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support\renderers;

use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer;

/**
 * Minimal concrete {@see ApiRenderer} used to exercise markdown helpers that
 * depend on a renderer being set (links, inline tags, api links).
 */
class StubApiRenderer extends ApiRenderer
{
    public function init()
    {
        parent::init();
        $this->apiContext = new Context();
        $this->guideUrl = 'guide';
    }

    public function render($context, $targetDir)
    {
        return '';
    }

    public function generateApiUrl($typeName)
    {
        return str_replace('\\', '-', ltrim((string) $typeName, '\\')) . '.html';
    }

    protected function generateLink($text, $href, $options = [])
    {
        return '<a href="' . $href . '">' . $text . '</a>';
    }
}
