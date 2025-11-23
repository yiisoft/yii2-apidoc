<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\web;

/**
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * @phpstan-import-type PublishOptions from AssetManager
 * @psalm-import-type PublishOptions from AssetManager
 */
class AssetBundle
{
    /**
     * @var PublishOptions the options to be passed to [[AssetManager::publish()]] when the asset bundle
     * is being published.
     */
    public $publishOptions = [];
}
