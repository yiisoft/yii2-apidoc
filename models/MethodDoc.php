<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Method;

/**
 * Represents API documentation information for a `method`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MethodDoc extends FunctionDoc
{
    public $isAbstract;
    public $isFinal;
    public $isStatic;
    public $visibility;
    /**
     * @var string|null
     */
    public $definedBy;
    /**
     * @var string
     */
    public $sourceCode = '';


    /**
     * @param TypeDoc $parent
     * @param Class_|Method|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($parent, $reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->isAbstract = $reflector->isAbstract();
        $this->isFinal = $reflector->isFinal();
        $this->isStatic = $reflector->isStatic();

        $this->visibility = (string) $reflector->getVisibility();

        $lines = file($this->sourceFile);
        for ($i = $this->startLine - 1; $i <= $this->endLine - 1; $i++) {
            $this->sourceCode .= substr($lines[$i], 4);
        }
    }
}
