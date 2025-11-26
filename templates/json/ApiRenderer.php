<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\json;

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;

/**
 * The class for outputting documentation data structures as a JSON text.
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @since 2.0.5
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
        foreach ($types as $name => $type) {
            $types[$name] = (array) $this->removeParentFieldsRecursive($type);
            if ($type instanceof ClassDoc) {
                $types[$name]['type'] = 'class';
            } elseif ($type instanceof InterfaceDoc) {
                $types[$name]['type'] = 'interface';
            } elseif ($type instanceof TraitDoc) {
                $types[$name]['type'] = 'trait';
            }
        }
        file_put_contents($targetDir . '/types.json', json_encode($types, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    /**
     * Removes the `parent` fields recursively so that the data could be converted to JSON format..
     * @param array|object $data
     * @return array|object
     */
    private function removeParentFieldsRecursive($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'parent') {
                    unset($data[$key]);
                } elseif (is_object($value) || is_array($value)) {
                    $data[$key] = $this->removeParentFieldsRecursive($value);
                }
            }

            return $data;
        }

        foreach (get_object_vars($data) as $key => $value) {
            if ($key === 'parent') {
                unset($data->$key);
            } elseif (is_object($value) || is_array($value)) {
                $data->$key = $this->removeParentFieldsRecursive($value);
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
    }

    /**
     * @inheritdoc
     */
    protected function generateFileName($typeName)
    {
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function getSourceUrl($type, $line = null)
    {
    }
}
