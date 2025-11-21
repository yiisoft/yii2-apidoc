<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Argument;
use yii\apidoc\helpers\TypeHelper;
use yii\base\BaseObject;

/**
 * Represents API documentation information for a [[FunctionDoc|function]] or [[MethodDoc|method]] `param`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ParamDoc extends BaseObject
{
    public $name;
    public $isOptional;
    /**
     * @var string|null
     */
    public $defaultValue;
    public $isPassedByReference;
    // will be set by creating class
    public $description;
    /**
     * @var string[]|null
     */
    public $types;
    public $sourceFile;
    /**
     * @var FunctionDoc|MethodDoc
     */
    public $parent;

    /**
     * @param FunctionDoc|MethodDoc $parent
     * @param Argument|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        $this->parent = $parent;

        if ($reflector !== null) {
            $this->name = $reflector->getName();

            if ($this->types === null) {
                $this->types = TypeHelper::splitType($reflector->getType());
            }

            if (PHP_VERSION_ID >= 80100) {
                $reflectorDefault = $reflector->getDefault(false);
                $this->defaultValue = $reflectorDefault !== null ? (string) $reflectorDefault : null;
            } else {
                $this->defaultValue = $reflector->getDefault();
            }

            $this->isOptional = $this->defaultValue !== null;
            $this->isPassedByReference = $reflector->isByReference();
        }

        $this->name = '$' . $this->name;
    }
}
