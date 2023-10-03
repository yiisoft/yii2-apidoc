<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\project;

use Yii;
use yii\apidoc\helpers\ApiIndexer;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends \yii\apidoc\templates\bootstrap\ApiRenderer
{
    use \yii\apidoc\templates\bootstrap\RendererTrait;

    public $layout = '@yii/apidoc/templates/bootstrap/layouts/api.php';
	public $indexView = '@yii/apidoc/templates/bootstrap/views/index.php';

    /**
     * @inheritdoc
     */
    public function render($context, $targetDir)
    {
        // render view files
        parent::render($context, $targetDir);

        if ($this->controller !== null) {
            $this->controller->stdout('generating extension index files...');
        }
		
		$types = array_merge($context->classes, $context->interfaces, $context->traits);

		$appTypes = $this->filterTypes($types, 'app');

		// It's a hack, but we'll go with it for now.
		$readme = @file_get_contents($this->readmeUrl);
		$indexFileContent = $this->renderWithLayout($this->indexView, [
			'docContext' => $context,
			'types' => $appTypes ?: null,
			'readme' => $readme ?: null,
		]);
	
		file_put_contents($targetDir . '/index.html', $indexFileContent);
    }
}
