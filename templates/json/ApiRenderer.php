<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\json;

use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;
use yii\helpers\Html;
use yii\web\View;
use Yii;

/**
 * The class for outputting documentation data structures as a JSON text.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends BaseApiRenderer implements ViewContextInterface
{

    /**
     * Writes a given [[Context]] as JSON text to file 'types.json'.
     *
     * @param Context $context the api documentation context to render.
     * @param $targetDir
     */
    public function render($context, $targetDir)
    {
        $types = array_merge($context->classes, $context->interfaces, $context->traits);
        file_put_contents($targetDir . '/types.json', json_encode($types, JSON_PRETTY_PRINT));
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
        return $this->generateFileName($typeName);
    }

    /**
     * Generates file name for API page for a given type
     * @param string $typeName
     * @return string
     */
    protected function generateFileName($typeName)
    {
        return strtolower(str_replace('\\', '-', $typeName)) . '.html';
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return Yii::getAlias('@yii/apidoc/templates/html/views');
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
        $options['href'] = $href;

        return Html::a($text, null, $options);
    }

    /**
     * @inheritdoc
     */
    public function getSourceUrl($type, $line = null)
    {
        return null;
    }
}
