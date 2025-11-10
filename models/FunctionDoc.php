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
use yii\helpers\StringHelper;

/**
 * Represents API documentation information for a `function`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 *
 * @template TParent of (BaseDoc|null)
 * @extends BaseDoc<TParent>
 */
class FunctionDoc extends BaseDoc
{
    /**
     * @var ParamDoc[]
     */
    public $params = [];
    public $exceptions = [];
    public $return;
    public $returnType;
    public $returnTypes;
    public $isReturnByReference;


    /**
     * @param TParent $parent
     * @param Method $reflector
     * @param Context $context
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

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Throws) {
                $this->exceptions[implode($this->splitTypes($tag->getType()))] = $tag->getDescription();
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
                $this->params[$paramName]->types = $this->splitTypes($tag->getType());
                unset($this->tags[$i]);
            } elseif ($tag instanceof Return_) {
                $this->returnType = (string) $tag->getType();
                $this->returnTypes = $this->splitTypes($tag->getType());
                $this->return = StringHelper::mb_ucfirst($tag->getDescription());
                unset($this->tags[$i]);
            }
        }
    }
}
