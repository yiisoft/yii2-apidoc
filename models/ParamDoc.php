<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Argument;
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
    public $typeHint;
    public $isOptional;
    public $defaultValue;
    public $isPassedByReference;
    // will be set by creating class
    public $description;
    public $type;
    public $types;
    public $sourceFile;
    /**
     * @var FunctionDoc|MethodDoc
     */
    public $parent;

    /**
     * @param FunctionDoc|MethodDoc $parent
     * @param Argument $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        $this->parent = $parent;

        if ($reflector !== null) {
            $this->name = $reflector->getName();
            $this->typeHint = (string) $reflector->getType();
            $this->defaultValue = $reflector->getDefault();
            $this->isOptional = $this->defaultValue !== null;
            $this->isPassedByReference = $reflector->isByReference();
        }

        $this->name = '$' . $this->name ;

        if ($this->typeHint === 'mixed') {
            $this->typeHint = '';
        }
    }
}
