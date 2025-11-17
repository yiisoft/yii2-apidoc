<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\helpers\TypeAnalyzer;
use yii\apidoc\models\BaseDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\PseudoTypeDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\models\TypeDoc;
use yii\base\Component;
use yii\console\Controller;

/**
 * Base class for all documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class BaseRenderer extends Component
{
    /**
     * @deprecated since 2.0.1 use [[$guidePrefix]] instead which allows configuring this options
     */
    const GUIDE_PREFIX = 'guide-';

    private const PHP_CLASS_BASE_URL = 'https://www.php.net/class.';
    private const PHP_TYPE_BASE_URL = 'https://www.php.net/language.types.';
    private const PHPSTAN_TYPE_BASE_URL = 'https://phpstan.org/writing-php-code/phpdoc-types#';

    /**
     * @var string[]
     */
    private const PHP_TYPES = [
        'callable',
        'array',
        'string',
        'boolean',
        'bool',
        'integer',
        'int',
        'float',
        'object',
        'resource',
        'null',
        'false',
        'true',
        'iterable',
        'mixed',
        'never',
        'void',
    ];

    /**
     * @var array<string, string[]>
     */
    private const PHPSTAN_TYPES_DOC_LINKS = [
        'general-arrays' => [
            'array',
            'non-empty-array',
        ],
        'lists' => [
            'list',
            'non-empty-list',
        ],
        'basic-types' => [
            'array-key',
            'double',
            'number',
            'scalar',
        ],
        'class-string' => [
            'class-string',
        ],
        'other-advanced-string-types' => [
            'callable-string',
            'numeric-string',
            'non-empty-string',
            'non-falsy-string',
            'truthy-string',
            'literal-string',
            'lowercase-string',
        ],
        'integer-ranges' => [
            'int',
            'positive-int',
            'negative-int',
            'non-positive-int',
            'non-negative-int',
            'non-zero-int',
        ],
        'integer-masks' => [
            'int-mask',
            'int-mask-of',
        ],
        'bottom-type' => [
            'never-return',
            'never-returns',
            'no-return',
        ],
        'key-and-value-types-of-arrays-and-iterables' => [
            'key-of',
            'value-of',
        ],
    ];

    /**
     * @var string[]
     */
    private const PHP_TYPE_ALIASES = [
        'true' => 'boolean',
        'false' => 'boolean',
        'bool' => 'boolean',
        'int' => 'integer',
    ];

    /**
     * @var string[]
     */
    private const PHP_TYPE_DISPLAY_ALIASES = [
        'bool' => 'boolean',
        'int' => 'integer',
    ];

    public $guidePrefix = 'guide-';
    public $apiUrl;
    /**
     * @var string string to use as the title of the generated page.
     */
    public $pageTitle;
    /**
     * @var Context the [[Context]] currently being rendered.
     */
    public $apiContext;
    /**
     * @var Controller the apidoc controller instance. Can be used to control output.
     */
    public $controller;
    public $guideUrl;

    private TypeAnalyzer $typeAnalyzer;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->typeAnalyzer = new TypeAnalyzer();
    }

    public function init()
    {
        ApiMarkdown::$renderer = $this;
        ApiMarkdownLaTeX::$renderer = $this;
    }

    /**
     * creates a link to a type (class, interface or trait)
     * @param ClassDoc|InterfaceDoc|TraitDoc|ClassDoc[]|InterfaceDoc[]|TraitDoc[]|string|string[] $types
     * @param BaseDoc|null $context
     * @param string $title a title to be used for the link TODO check whether [[yii\...|Class]] is supported
     * @param array $options additional HTML attributes for the link.
     * @return string
     */
    public function createTypeLink($types, $context = null, $title = null, $options = [])
    {
        if (!is_array($types)) {
            $types = [$types];
        } elseif (count($types) > 1) {
            $title = null;
        }

        $links = [];
        foreach ($types as $type) {
            if (is_string($type) && $type !== '' && !in_array($type, self::PHP_TYPES)) {
                if ($this->typeAnalyzer->isIntersectionType($type)) {
                    $innerTypes = $this->typeAnalyzer->getTypesByIntersectionType($type);
                    $innerTypesLinks = [];

                    foreach ($innerTypes as $innerType) {
                        $innerTypesLinks[] = $this->createTypeLink($innerType, $context, $title, $options);
                    }

                    $links[] = implode('&amp;', $innerTypesLinks);
                    continue;
                } elseif (substr($type, -3, 3) === ')[]') {
                    $arrayTypes = $this->createTypeLink(
                        $this->typeAnalyzer->getTypesByArrayType($type),
                        $context,
                        $title,
                        $options
                    );

                    $links[] = "({$arrayTypes})[]";
                    continue;
                } elseif (substr($type, -2, 2) === '[]') {
                    $arrayElementType = substr($type, 0, -2);
                    $templateType = $this->getTemplateType($arrayElementType, $context);

                    if ($templateType !== null) {
                        $templateTypes = $this->typeAnalyzer->getChildTypesByType($templateType);
                        $typeLink = $this->createTypeLink($templateTypes, $context, $title, $options);

                        if (count($templateTypes) > 1) {
                            $links[] = "({$typeLink})[]";
                        } else {
                            $links[] =  "{$typeLink}[]";
                        }
                    } else {
                        $links[] = $this->createTypeLink($arrayElementType, $context, $title, $options) . '[]';
                    }
                    continue;
                } elseif (($typeDoc = $this->apiContext->getType(ltrim($type, '\\'))) !== null) {
                    $links[] = $this->createTypeLink($typeDoc, $context, $typeDoc->name, $options);
                    continue;
                } elseif (
                    $type[0] !== '\\' &&
                    ($typeDoc = $this->apiContext->getType($this->resolveNamespace($context) . '\\' . ltrim($type, '\\'))) !== null
                ) {
                    $links[] = $this->createTypeLink($typeDoc, $context, $typeDoc->name, $options);
                    continue;
                } elseif (($templateType = $this->getTemplateType($type, $context)) !== null) {
                    $links[] = $this->createTypeLink(
                        $this->typeAnalyzer->getChildTypesByType($templateType),
                        $context,
                        $title,
                        $options
                    );
                    continue;
                } elseif (($phpStanType = $this->getPhpStanType($type, $context)) !== null) {
                    $links[] = $this->createSubjectLink($phpStanType);
                    continue;
                } elseif (($psalmType = $this->getPsalmType($type, $context)) !== null) {
                    $links[] = $this->createSubjectLink($psalmType);
                    continue;
                } elseif ($this->typeAnalyzer->isGenericType($type)) {
                    $genericTypes = $this->typeAnalyzer->getTypesByGenericType($type);
                    $typesLinks = [];

                    foreach ($genericTypes as $genericType) {
                        $typesLinks[] = $this->createTypeLink(
                            $genericType,
                            $context,
                            $title,
                            $options
                        );
                    }

                    $mainType = substr($type, 0, strpos($type, '<'));
                    $mainTypeLink = $this->createTypeLink(
                        $mainType,
                        $context,
                        $title,
                        array_merge($options, ['forcePhpStanLink' => true])
                    );

                    $links[] = "{$mainTypeLink}&lt;" . implode(', ', $typesLinks) . '&gt;';
                    continue;
                }
            }

            if (is_object($type) && method_exists($type, '__toString')) {
                $type = (string) $type;
            }

            $link = $this->createTypeLinkByType($type, $title, $options);
            if ($link !== null) {
                $links[] = $link;
            }
        }

        return implode('|', array_unique($links));
    }

    /**
     * @param MethodDoc $method
     * @param TypeDoc $type
     * @return string
     */
    public function createMethodReturnTypeLink($method, $type)
    {
        if (!($type instanceof ClassDoc) || $type->isAbstract) {
            return $this->createTypeLink($method->returnTypes, $method);
        }

        $returnTypes = [];
        foreach ($method->returnTypes as $returnType) {
            if ($returnType !== 'static' && $returnType !== 'static[]') {
                $returnTypes[] = $returnType;

                continue;
            }

            $context = $this->apiContext;
            if (isset($context->interfaces[$method->definedBy]) || isset($context->traits[$method->definedBy])) {
                $replacement = $type->name;
            } else {
                $replacement = $method->definedBy;
            }

            $returnTypes[] = str_replace('static', $replacement, $returnType);
        }

        return $this->createTypeLink($returnTypes, $method);
    }

    /**
     * creates a link to a subject
     * @param PropertyDoc|MethodDoc|ConstDoc|EventDoc|PseudoTypeDoc $subject
     * @param string|null $title
     * @param array $options additional HTML attributes for the link.
     * @param TypeDoc|null $type
     * @return string
     */
    public function createSubjectLink($subject, $title = null, $options = [], $type = null)
    {
        if ($subject instanceof PseudoTypeDoc) {
            $href = $this->generateApiUrl($subject->parent->name) . "#{$subject->type}-type-{$subject->name}";
            return $this->generateLink($subject->name, $href, $options);
        }

        if ($title === null) {
            if ($subject instanceof MethodDoc) {
                $title = $subject->name . '()';
            } else {
                $title = $subject->name;
            }
        }

        if (!$type) {
            $type = $this->apiContext->getType($subject->definedBy);
        }

        if (!$type) {
            return $subject->name;
        }

        $link = $this->generateApiUrl($type->name) . '#' . $subject->name;
        if ($subject instanceof MethodDoc) {
            $link .= '()';
        }

        $link .= '-detail';

        return $this->generateLink($title, $link, $options);
    }

    /**
     * @param BaseDoc|string $context
     * @return string
     */
    private function resolveNamespace($context)
    {
        // TODO use phpdoc Context for this
        if ($context === null) {
            return '';
        }
        if ($context instanceof TypeDoc) {
            return $context->namespace;
        }
        if ($context->hasProperty('definedBy') && method_exists($context, '__toString')) {
            $type = $this->apiContext->getType($context);
            if ($type !== null) {
                return $type->namespace;
            }
        }

        return '';
    }

    /**
     * generate link markup
     * @param $text
     * @param $href
     * @param array $options additional HTML attributes for the link.
     * @return mixed
     */
    abstract protected function generateLink($text, $href, $options = []);

    /**
     * Generate an url to a type in apidocs
     * @param $typeName
     * @return mixed
     */
    abstract public function generateApiUrl($typeName);

    /**
     * Generate an url to a guide page
     * @param string $file
     * @return string
     */
    public function generateGuideUrl($file)
    {
        //skip parsing external url
        if ((strpos($file, 'https://') !== false) || (strpos($file, 'http://') !== false)) {
            return $file;
        }

        $hash = '';
        if (($pos = strpos($file, '#')) !== false) {
            $hash = substr($file, $pos);
            $file = substr($file, 0, $pos);
        }

        return rtrim($this->guideUrl, '/') . '/' . $this->guidePrefix . basename($file, '.md') . '.html' . $hash;
    }

    /**
     * @param BaseDoc|string $type
     */
    private function createTypeLinkByType($type, ?string $title = null, array $options = []): ?string
    {
        if (isset($options['forcePhpStanLink'])) {
            $isForcePhpStanLink = $options['forcePhpStanLink'];
            unset($options['forcePhpStanLink']);
        } else {
            $isForcePhpStanLink = false;
        }

        if (is_string($type)) {
            $linkText = ltrim($type, '\\');
            if ($title !== null) {
                $linkText = $title;
                $title = null;
            }

            // check if it is PHP internal class
            if (
                (class_exists($type, false) || interface_exists($type, false) || trait_exists($type, false)) &&
                ($reflection = new \ReflectionClass($type)) && $reflection->isInternal()
            ) {
                return $this->generateLink(
                    $linkText,
                    self::PHP_CLASS_BASE_URL . strtolower(ltrim($type, '\\')),
                    $options
                );
            }

            if ($isForcePhpStanLink) {
                $link = $this->generatePhpStanTypeLink($type, $options);
                $link ??= $this->generatePhpTypeLink($type, $linkText, $options);
            } else {
                $link = $this->generatePhpTypeLink($type, $linkText, $options);
                $link ??= $this->generatePhpStanTypeLink($type, $options);
            }

            return $link ?? $type;
        }

        if ($type instanceof BaseDoc) {
            $linkText = $type->name;
            if ($title !== null) {
                $linkText = $title;
                $title = null;
            }

            return $this->generateLink($linkText, $this->generateApiUrl($type->name), $options);
        }

        return null;
    }

    private function getFqcnLastPart(string $fqcn): string
    {
        $backslashPosition = strrpos($fqcn, '\\');
        if ($backslashPosition === false) {
            return $fqcn;
        }

        return substr($fqcn, $backslashPosition + 1);
    }

    private function getPhpStanType(string $type, ?BaseDoc $context): ?PseudoTypeDoc
    {
        if ($context === null) {
            return null;
        }

        $phpStanType = $context->phpStanTypes[$this->getFqcnLastPart($type)] ?? null;
        if ($phpStanType === null) {
            return $context->parent !== null ? $this->getPhpStanType($type, $context->parent) : null;
        }

        return $phpStanType;
    }

    private function getPsalmType(string $type, ?BaseDoc $context): ?PseudoTypeDoc
    {
        if ($context === null) {
            return null;
        }

        $psalmType = $context->psalmTypes[$this->getFqcnLastPart($type)] ?? null;
        if ($psalmType === null) {
            return $context->parent !== null ? $this->getPsalmType($type, $context->parent) : null;
        }

        return $psalmType;
    }

    private function getTemplateType(string $type, ?BaseDoc $context): ?string
    {
        if ($context === null) {
            return null;
        }

        $template = $context->templates[$this->getFqcnLastPart($type)] ?? null;
        if ($template === null) {
            return $context->parent !== null ? $this->getTemplateType($type, $context->parent) : null;
        }

        return (string) $template->getBound();
    }

    private function generatePhpTypeLink(string $type, string $linkText, array $options): ?string
    {
        if (in_array($type, self::PHP_TYPES)) {
            if (isset(self::PHP_TYPE_DISPLAY_ALIASES[$type])) {
                $linkText = self::PHP_TYPE_DISPLAY_ALIASES[$type];
            }

            if (isset(self::PHP_TYPE_ALIASES[$type])) {
                $type = self::PHP_TYPE_ALIASES[$type];
            }

            return $this->generateLink(
                $linkText,
                self::PHP_TYPE_BASE_URL . strtolower(ltrim($type, '\\')),
                $options
            );
        }

        return null;
    }

    private function generatePhpStanTypeLink(string $type, array $options): ?string
    {
        foreach (self::PHPSTAN_TYPES_DOC_LINKS as $phpstanDocLink => $phpstanTypes) {
            if (in_array($type, $phpstanTypes)) {
                return $this->generateLink(
                    $type,
                    self::PHPSTAN_TYPE_BASE_URL . $phpstanDocLink,
                    $options
                );
            }
        }

        return null;
    }
}
