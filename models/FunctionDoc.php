<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\Php\Method;
use phpDocumentor\Reflection\Type;
use yii\helpers\StringHelper;

/**
 * Represents API documentation information for a `function`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class FunctionDoc extends BaseDoc
{
    /**
     * @var ParamDoc[]
     */
    public $params = [];
    /**
     * @var Throws[]
     */
    public $exceptions = [];
    /**
     * @var string|null
     */
    public $return;
    /**
     * @var Type|null
     */
    public $returnType;
    /**
     * @var bool
     */
    public $isReturnByReference;

    /**
     * @param BaseDoc|null $parent
     * @param Method|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($parent, $reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->isReturnByReference = $reflector->getHasReturnByReference();

        foreach ($reflector->getArguments() as $arg) {
            $arg = new ParamDoc($this, $arg, $context, ['sourceFile' => $this->sourceFile]);
            $this->params[$arg->name] = $arg;
        }

        $hasInheritdoc = false;

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Throws) {
                $this->exceptions[] = $tag;
                unset($this->tags[$i]);
            } elseif ($tag instanceof Param) {
                $paramName = '$' . $tag->getVariableName();
                if (!isset($this->params[$paramName]) && $context !== null) {
                    $context->errors[] = [
                        'line' => $this->startLine,
                        'file' => $this->sourceFile,
                        'message' => "Undefined parameter documented: $paramName in {$this->name}().",
                    ];
                    continue;
                }

                $this->params[$paramName]->description = StringHelper::mb_ucfirst($tag->getDescription());
                $this->params[$paramName]->type = $tag->getType();
                unset($this->tags[$i]);
            } elseif ($tag instanceof Return_) {
                $this->returnType = $tag->getType();
                $this->return = StringHelper::mb_ucfirst($tag->getDescription());
                unset($this->tags[$i]);
            } elseif ($this->isInheritdocTag($tag)) {
                $hasInheritdoc = true;
            }
        }

        if (!$hasInheritdoc && $this->returnType === null) {
            $this->returnType = $reflector->getReturnType();
        }
    }
}
