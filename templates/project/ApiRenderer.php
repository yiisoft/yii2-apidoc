<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\project;

use Yii;
use yii\helpers\Console;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 4.0
 */
class ApiRenderer extends \yii\apidoc\templates\bootstrap\ApiRenderer
{
    /**
     * @inheritdoc
     */
    public function render($context, $targetDir)
    {
        // render view files
        parent::render($context, $targetDir);

        if ($this->controller !== null) {
            $this->controller->stdout('rendering the index file...');
        }

        $types = array_merge($context->classes, $context->interfaces, $context->traits);
        $appTypes = $this->filterTypes($types, 'app');
        $readme = @file_get_contents($this->readmeUrl);

        $indexFileContent = $this->renderWithLayout($this->indexView, [
            'docContext' => $context,
            'types' => $appTypes,
            'readme' => $readme ?: null,
        ]);

        file_put_contents($targetDir . '/index.html', $indexFileContent);

        if ($this->controller !== null) {
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }
}
