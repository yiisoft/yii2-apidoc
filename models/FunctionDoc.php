<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\Php\Method;
use phpDocumentor\Reflection\Php\Property;
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
    public $returnType;
    public $returnTypes;
    public $isReturnByReference;


    /**
     * @param Method $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->isReturnByReference = $reflector->getNode()->byRef;

        foreach ($reflector->getArguments() as $arg) {
            $arg = new ParamDoc($arg, $context, ['sourceFile' => $this->sourceFile]);
            $this->params[$arg->name] = $arg;
        }

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Throws) {
                $this->exceptions[(string) $tag->getType()->getFqsen()] = $tag->getDescription();
                unset($this->tags[$i]);
            } elseif ($tag instanceof Param) {
                $paramName = $tag->getVariableName();
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
                $this->params[$paramName]->types = $this->getTagTypes($tag);
                unset($this->tags[$i]);
            } elseif ($tag instanceof Return_) {
                $this->returnType = (string) $tag->getType();
                $this->returnTypes = $this->getTagTypes($tag);
                $this->return = StringHelper::mb_ucfirst($tag->getDescription());
                unset($this->tags[$i]);
            }
        }
    }
}
