<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\Php\Class_;
use yii\apidoc\helpers\PhpDocTagParser;
use yii\apidoc\helpers\TypeAnalyzer;
use yii\apidoc\models\types\ConditionalReturnType;
use yii\helpers\StringHelper;

/**
 * Base class for API documentation information for classes, interfaces and traits.
 *
 * @property-read MethodDoc[] $nativeMethods
 * @property-read PropertyDoc[] $nativeProperties
 * @property-read MethodDoc[] $protectedMethods
 * @property-read PropertyDoc[] $protectedProperties
 * @property-read MethodDoc[] $publicMethods
 * @property-read PropertyDoc[] $publicProperties
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class TypeDoc extends BaseDoc
{
    public $authors = [];
    /**
     * @var MethodDoc[]
     */
    public $methods = [];
    /**
     * @var PropertyDoc[]
     */
    public $properties = [];
    public $namespace;


    /**
     * Finds subject (method or property) by name
     *
     * If there is a property with the same as a method, the method will be returned if the name is not stated
     * explicitly by prefixing with `$`.
     *
     * Example for method `attributes()` and property `$attributes` which both may exist:
     *
     * - `$subjectName = '$attributes'` finds a property or nothing.
     * - `$subjectName = 'attributes()'` finds a method or nothing.
     * - `$subjectName = 'attributes'` finds the method if it exists, if not it will find the property.
     *
     * @param $subjectName
     * @return null|MethodDoc|PropertyDoc
     */
    public function findSubject($subjectName)
    {
        if (empty($subjectName)) {
            return null;
        }

        $subjectName = ltrim(str_replace($this->namespace, '', $subjectName), '\\');
        if ($subjectName[0] !== '$') {
            foreach ($this->methods as $name => $method) {
                if (rtrim($subjectName, '()') == $name) {
                    return $method;
                }
            }
        }
        if (substr_compare($subjectName, '()', -2, 2) === 0) {
            return null;
        }
        if ($this->properties === null) {
            return null;
        }
        foreach ($this->properties as $name => $property) {
            if (ltrim($subjectName, '$') == ltrim($name, '$')) {
                return $property;
            }
        }

        return null;
    }

    /**
     * @return MethodDoc[]
     */
    public function getNativeMethods()
    {
        return $this->getFilteredMethods(null, $this->name);
    }

    /**
     * @return MethodDoc[]
     */
    public function getPublicMethods()
    {
        return $this->getFilteredMethods('public');
    }

    /**
     * @return MethodDoc[]
     */
    public function getProtectedMethods()
    {
        return $this->getFilteredMethods('protected');
    }

    /**
     * @param string|null $visibility
     * @param string|null $definedBy
     * @return MethodDoc[]
     */
    private function getFilteredMethods($visibility = null, $definedBy = null)
    {
        $methods = [];
        foreach ($this->methods as $name => $method) {
            if ($visibility !== null && $method->visibility != $visibility) {
                continue;
            }
            if ($definedBy !== null && $method->definedBy != $definedBy) {
                continue;
            }
            $methods[$name] = $method;
        }

        return $methods;
    }

    /**
     * @return PropertyDoc[]
     */
    public function getNativeProperties()
    {
        return $this->getFilteredProperties(null, $this->name);
    }

    /**
     * @return PropertyDoc[]
     */
    public function getPublicProperties()
    {
        return $this->getFilteredProperties('public');
    }

    /**
     * @return PropertyDoc[]
     */
    public function getProtectedProperties()
    {
        return $this->getFilteredProperties('protected');
    }

    /**
     * @param null $visibility
     * @param null $definedBy
     * @return PropertyDoc[]
     */
    private function getFilteredProperties($visibility = null, $definedBy = null)
    {
        if ($this->properties === null) {
            return [];
        }
        $properties = [];
        foreach ($this->properties as $name => $property) {
            if ($visibility !== null && $property->visibility != $visibility) {
                continue;
            }
            if ($definedBy !== null && $property->definedBy != $definedBy) {
                continue;
            }
            $properties[$name] = $property;
        }

        return $properties;
    }

    /**
     * @param Class_ $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct(null, $reflector, $context, $config);

        $this->namespace = trim(StringHelper::dirname($this->name), '\\');

        if ($reflector === null) {
            return;
        }

        $typeAnalyzer = new TypeAnalyzer();
        $phpDocTagParser = new PhpDocTagParser();

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Author) {
                $this->authors[$tag->getAuthorName()] = $tag->getEmail();
                unset($this->tags[$i]);
            }

            if ($tag instanceof Property || $tag instanceof PropertyRead || $tag instanceof PropertyWrite) {
                $shortDescription = $tag->getDescription() ? BaseDoc::extractFirstSentence($tag->getDescription()) : '';
                $name = '$' . $tag->getVariableName();

                $property = new PropertyDoc($this, null, $context, [
                    'sourceFile' => $this->sourceFile,
                    'name' => $name,
                    'fullName' => ltrim((string) $reflector->getFqsen(), '\\') . '::' . $name,
                    'isStatic' => false,
                    'visibility' => 'public',
                    'definedBy' => $this->name,
                    'type' => (string) $tag->getType(),
                    'types' => $this->splitTypes($tag->getType()),
                    'shortDescription' => $shortDescription,
                    'description' => $tag->getDescription(),
                ]);

                $this->properties[$property->name] = $property;
            }

            if ($tag instanceof Method) {
                $params = [];

                foreach ($tag->getParameters() as $parameter) {
                    $argumentType = (string) $parameter->getType();

                    $params[] = new ParamDoc($tag, null, $context, [
                        'sourceFile' => $this->sourceFile,
                        'name' => $parameter->getName(),
                        'typeHint' => $argumentType,
                        'type' => $argumentType,
                        'types' => [],
                    ]);
                }

                $returnType = null;

                // We are trying to retrieve a conditional type because PHPDocumentor converts the
                // conditional type to mixed
                if ((string) $tag->getReturnType() === 'mixed') {
                    $docBlockEndLineNumber = $reflector->getLocation()->getLineNumber() - 2;
                    $lines = file($this->sourceFile);

                    $docBlockIterator = $docBlockEndLineNumber;
                    while ($docBlockIterator > 0) {
                        if (
                            strpos($lines[$docBlockIterator], '@method') !== false &&
                            strpos($lines[$docBlockIterator], $tag->getMethodName() . '(') !== false
                        ) {
                            $realType = $phpDocTagParser->getTypeFromMethodTag(trim($lines[$docBlockIterator], ' *'));

                            if ($realType !== 'mixed' && $typeAnalyzer->isConditionalType($realType)) {
                                $returnType = $realType;
                                $returnTypes = $typeAnalyzer->getPossibleTypesByConditionalType($realType);
                            }

                            break;
                        }

                        $docBlockIterator--;
                    }
                }

                if ($returnType === null) {
                    $returnType = (string) $tag->getReturnType();
                    $returnTypes = $this->splitTypes($tag->getReturnType());
                }

                $shortDescription = $tag->getDescription() ? BaseDoc::extractFirstSentence($tag->getDescription()) : '';
                $description = $shortDescription ? substr($tag->getDescription(), strlen($shortDescription)) : '';

                $method = new MethodDoc($this, null, $context, [
                    'sourceFile' => $this->sourceFile,
                    'name' => $tag->getMethodName(),
                    'fullName' => ltrim((string) $reflector->getFqsen(), '\\') . '::' . $tag->getMethodName(),
                    'shortDescription' => $shortDescription,
                    'description' => $description,
                    'visibility' => 'public',
                    'params' => $params,
                    'isStatic' => $tag->isStatic(),
                    'return' => ' ',
                    'returnType' => $returnType,
                    'returnTypes' => $returnTypes,
                ]);
                $method->definedBy = $this->name;
                $this->methods[$method->name] = $method;
            }
        }

        $this->initProperties($reflector, $context);

        foreach ($reflector->getMethods() as $methodReflector) {
            if ($methodReflector->getDocBlock() && $methodReflector->getDocBlock()->hasTag('internal')) {
                continue;
            }

            if ((string) $methodReflector->getVisibility() !== 'private') {
                $method = new MethodDoc($this, $methodReflector, $context, ['sourceFile' => $this->sourceFile]);
                $method->definedBy = $this->name;
                $this->methods[$method->name] = $method;
            }
        }

        if ($context !== null) {
            $context->addErrorsByExceptions($typeAnalyzer->getExceptions());
            $context->addErrorsByExceptions($phpDocTagParser->getExceptions());
        }
    }

    /**
     * @param Class_ $reflector
     * @param Context $context
     */
    protected function initProperties($reflector, $context)
    {
        foreach ($reflector->getProperties() as $propertyReflector) {
            if ($propertyReflector->getDocBlock() && $propertyReflector->getDocBlock()->hasTag('internal')) {
                continue;
            }

            if ((string) $propertyReflector->getVisibility() !== 'private') {
                $property = new PropertyDoc($this, $propertyReflector, $context, ['sourceFile' => $this->sourceFile]);
                $property->definedBy = $this->name;
                $this->properties[$property->name] = $property;
            }
        }
    }
}
