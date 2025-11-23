<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\web;

use yiiunit\apidoc\data\api\base\Component;

/**
 * AssetManager manages asset bundle configuration and loading.
 *
 * @phpstan-type PublishOptions array{
 *     only?: string[],
 *     except?: string[],
 *     caseSensitive?: bool,
 *     beforeCopy?: callable,
 *     afterCopy?: callable,
 *     forceCopy?: bool,
 * }
 *
 * @psalm-type PublishOptions = array{
 *     only?: string[],
 *     except?: string[],
 *     caseSensitive?: bool,
 *     beforeCopy?: callable,
 *     afterCopy?: callable,
 *     forceCopy?: bool,
 * }
 */
class AssetManager extends Component
{
    /**
     * Publishes a file or a directory.
     *
     * @param non-empty-string $path the asset (file or directory) to be published
     * @param PublishOptions $options the options to be applied when publishing a directory.
     * @return non-empty-array the path (directory or file path) and the URL that the asset is published as.
     */
    public function publish($path, $options = [])
    {
    }
}
