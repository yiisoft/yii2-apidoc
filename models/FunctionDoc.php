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
use yii\apidoc\helpers\TypeHelper;
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
    public $exceptions = [];
    public $return;
    /**
     * @var string|null
     */
    public $returnType;
    /**
     * @var string[]|null
     */
    public $returnTypes;
    public $isReturnByReference;


    /**
     * @param Method|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->isReturnByReference = $reflector->getHasReturnByReference();

        foreach ($reflector->getArguments() as $arg) {
            $arg = new ParamDoc($arg, $context, ['sourceFile' => $this->sourceFile]);
            $this->params[$arg->name] = $arg;
        }

        $hasInheritdoc = false;

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Throws) {
                $this->exceptions[implode(TypeHelper::splitType($tag->getType()))] = $tag->getDescription();
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
                $this->params[$paramName]->type = (string) $tag->getType();
                $this->params[$paramName]->types = TypeHelper::splitType($tag->getType());
                unset($this->tags[$i]);
            } elseif ($tag instanceof Return_) {
                $this->returnType = (string) $tag->getType();
                $this->returnTypes = TypeHelper::splitType($tag->getType());
                $this->return = StringHelper::mb_ucfirst($tag->getDescription());
                unset($this->tags[$i]);
            } elseif ($this->isInheritdocTag($tag)) {
                $hasInheritdoc = true;
            }
        }

        if (!$hasInheritdoc && $this->returnType === null) {
            $this->returnType = (string) $reflector->getReturnType();
            $this->returnTypes = [$this->returnType];
        }
    }
}
