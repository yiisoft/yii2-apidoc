<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Argument;
use phpDocumentor\Reflection\Type;
use yii\base\BaseObject;

/**
 * Represents API documentation information for a [[FunctionDoc|function]] or [[MethodDoc|method]] `param`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ParamDoc extends BaseObject
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var bool|null
     */
    public $isOptional;
    /**
     * @var string|null
     */
    public $defaultValue;
    /**
     * @var bool|null
     */
    public $isPassedByReference;
    // will be set by creating class
    /**
     * @var string|null
     */
    public $description;
    /**
     * @var Type|null
     */
    public $type;
    /**
     * @var string|null
     */
    public $sourceFile;

    /**
     * @param FunctionDoc|MethodDoc $parent
     * @param Argument|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct(public $parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        if ($reflector !== null) {
            $this->name = $reflector->getName();

            if ($this->type === null) {
                $this->type = $reflector->getType();
            }

            $reflectorDefault = $reflector->getDefault(false);
            $this->defaultValue = $reflectorDefault !== null ? (string) $reflectorDefault : null;

            $this->isOptional = $this->defaultValue !== null;
            $this->isPassedByReference = $reflector->isByReference();
        }

        $this->name = '$' . $this->name;
    }
}
