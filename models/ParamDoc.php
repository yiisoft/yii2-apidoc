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
    /**
     * @var string|null
     */
    public $defaultValue;
    public $isPassedByReference;
    // will be set by creating class
    public $description;
    /**
     * @var string|null
     */
    public $type;
    /**
     * @var string[]|null
     */
    public $types;
    public $sourceFile;


    /**
     * @param Argument $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        if ($reflector !== null) {
            $this->name = $reflector->getName();
            $this->typeHint = (string) $reflector->getType();

            if ($this->type === null) {
                $this->type = $this->typeHint;
                $this->types = [$this->type];
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

        if ($this->typeHint === 'mixed') {
            $this->typeHint = '';
        }
    }
}
