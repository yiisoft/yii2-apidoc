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
use yii\apidoc\helpers\PhpDocTagParser;
use yii\apidoc\helpers\TypeAnalyzer;
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
    /**
     * @var array<string, Description|null>
     */
    public $exceptions = [];
    /**
     * @var string|null
     */
    public $return;
    /**
     * @var string|null
     */
    public $returnType;
    /**
     * @var string[]|null
     */
    public $returnTypes;
    /**
     * @var bool
     */
    public $isReturnByReference;


    /**
     * @param BaseDoc|null $parent
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

        $typeAnalyzer = new TypeAnalyzer();
        $phpDocTagParser = new PhpDocTagParser();

        $this->isReturnByReference = $reflector->getHasReturnByReference();

        foreach ($reflector->getArguments() as $arg) {
            $arg = new ParamDoc($this, $arg, $context, ['sourceFile' => $this->sourceFile]);
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
                // We are trying to retrieve a conditional type because PHPDocumentor converts the
                // conditional type to mixed
                if ((string) $tag->getType() === 'mixed') {
                    $docBlockEndLineNumber = $reflector->getLocation()->getLineNumber() - 2;
                    $lines = file($this->sourceFile);

                    $docBlockIterator = $docBlockEndLineNumber;
                    while ($docBlockIterator > 0) {
                        if (strpos($lines[$docBlockIterator], '@return') !== false) {
                            $realType = $phpDocTagParser->getTypeFromReturnTag(trim($lines[$docBlockIterator], ' *'));

                            if ($realType !== 'mixed' && $typeAnalyzer->isConditionalType($realType)) {
                                $this->returnType = $realType;
                                $this->returnTypes = $typeAnalyzer->getPossibleTypesByConditionalType($realType);
                            }

                            break;
                        }

                        $docBlockIterator--;
                    }
                }

                if ($this->returnType === null) {
                    $this->returnType = (string) $tag->getType();
                    $this->returnTypes = TypeHelper::splitType($tag->getType());
                }

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
