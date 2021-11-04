<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Argument;
use yii\apidoc\helpers\PrettyPrinter;
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
     * @param Argument $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        if ($reflector === null) {
            return;
        }

        $this->name = '$'. $reflector->getName();
        $this->typeHint = $reflector->getType();

//        if ($reflector->getDefault()) {
//            $this->defaultValue = PrettyPrinter::getRepresentationOfValue($reflector->getDefault());
//        }

        $this->isOptional = $reflector->getDefault() !== null;
        $this->isPassedByReference = $reflector->isByReference();
    }
}
