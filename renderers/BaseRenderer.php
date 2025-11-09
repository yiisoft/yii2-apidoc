<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\models\BaseDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
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

    public function init()
    {
        ApiMarkdown::$renderer = $this;
        ApiMarkdownLaTeX::$renderer = $this;
    }

    /**
     * creates a link to a type (class, interface or trait)
     * @param ClassDoc|InterfaceDoc|TraitDoc|ClassDoc[]|InterfaceDoc[]|TraitDoc[]|string|string[]|null $types
     * @param BaseDoc|null $context
     * @param string $title a title to be used for the link TODO check whether [[yii\...|Class]] is supported
     * @param array $options additional HTML attributes for the link.
     * @return string
     */
    public function createTypeLink($types, $context = null, $title = null, $options = [])
    {
        if ($types === null) {
            return '';
        }

        if (!is_array($types)) {
            $types = [$types];
        } elseif (count($types) > 1) {
            $title = null;
        }

        $links = [];
        foreach ($types as $type) {
            if (is_string($type) && $type !== '' && !in_array($type, self::PHP_TYPES)) {
                if (strpos($type, 'list<') !== false) {
                    $listTypes = $this->createTypeLink(
                        $this->extractTypesFromListType($type),
                        $context,
                        $title,
                        $options
                    );

                    $links[] = preg_replace('/^(non-empty-list|list)<.*?>$/', "$1&lt;{$listTypes}&gt;", $type);
                    break;
                } elseif (strpos($type, 'array<') !== false) {
                    $arrayTypes = $this->extractTypesFromArrayType($type);
                    $valueTypes = $this->createTypeLink(
                        $arrayTypes['valueTypes'],
                        $context,
                        $title,
                        $options
                    );

                    if ($arrayTypes['keyTypes']) {
                        $keyTypes = $this->createTypeLink(
                            $arrayTypes['keyTypes'],
                            $context,
                            $title,
                            $options
                        );

                        $links[] = preg_replace(
                            '/^(non-empty-array|array)<.*?>$/',
                            "$1&lt;{$keyTypes}, {$valueTypes}&gt;",
                            $type
                        );
                    } else {
                        $links[] = preg_replace(
                            '/^(non-empty-array|array)<.*?>$/',
                            "$1&lt;{$valueTypes}&gt;",
                            $type
                        );
                    }

                    break;
                } elseif (substr_compare($type, 'class-string<', 0, 13) === 0) {
                    $classStringTypes = $this->createTypeLink(
                        $this->extractTypesFromClassStringType($type),
                        $context,
                        $title,
                        $options
                    );

                    $links[] = "class-string&lt;{$classStringTypes}&gt;";
                    break;
                } elseif (substr_compare($type, ')[]', -3, 3) === 0) {
                    $arrayTypes = $this->createTypeLink(
                        $this->extractTypesFromArrayWithParenthesesType($type),
                        $context,
                        $title,
                        $options
                    );

                    $links[] = "({$arrayTypes})[]";
                    break;
                } elseif (substr_compare($type, '[]', -2, 2) === 0) {
                    $links[] = $this->createTypeLink(substr($type, 0, -2)) . '[]';
                    break;
                } elseif (substr_compare($type, 'array{', 0, 6) === 0) {
                    $type = 'array';
                } elseif (substr_compare($type, 'object{', 0, 7) === 0) {
                    $type = 'object';
                } elseif (substr_compare($type, 'int<', 0, 4) === 0) {
                    $type = 'integer';
                } elseif ($type === '$this' && $context instanceof TypeDoc) {
                    $title = '$this';
                    $type = $context;
                } elseif (($typeDoc = $this->apiContext->getType(ltrim($type, '\\'))) !== null) {
                    $type = $typeDoc;
                } elseif (
                    $type[0] !== '\\' &&
                    ($typeDoc = $this->apiContext->getType($this->resolveNamespace($context) . '\\' . ltrim($type, '\\'))) !== null
                ) {
                    $type = $typeDoc;
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
            return $this->createTypeLink($method->returnTypes, $type);
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

        return $this->createTypeLink($returnTypes, $type);
    }

    /**
     * creates a link to a subject
     * @param PropertyDoc|MethodDoc|ConstDoc|EventDoc $subject
     * @param string|null $title
     * @param array $options additional HTML attributes for the link.
     * @param TypeDoc|null $type
     * @return string
     */
    public function createSubjectLink($subject, $title = null, $options = [], $type = null)
    {
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
        if ($context->hasProperty('definedBy')) {
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

            return $type;
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

    /**
     * @return string[]
     */
    private function extractTypesFromArrayWithParenthesesType(string $type): array
    {
        preg_match('/^\((.+)\)\[\]$/', $type, $matches);

        return $this->extractTypesFromUnionType($matches[1]);
    }

    /**
     * @return string[]
     */
    private function extractTypesFromListType(string $type): array
    {
        preg_match('/(?:non-empty-)?(?:list)<([^>]+)>/', $type, $matches);

        return $this->extractTypesFromUnionType($matches[1]);
    }

    /**
     * @return array{
     *     keyTypes: string[],
     *     valueTypes: string[],
     * }
     */
    private function extractTypesFromArrayType(string $type): array
    {
        preg_match('/(?:non-empty-)?(?:array)<([^>]+)>/', $type, $matches);

        $arrayTypes = explode(',', $matches[1]);
        if (isset($arrayTypes[1])) {
            $keyTypes = $this->extractTypesFromUnionType($arrayTypes[0]);
            $valueTypes = $this->extractTypesFromUnionType(ltrim($arrayTypes[1]));
        } else {
            $keyTypes = [];
            $valueTypes = $this->extractTypesFromUnionType($arrayTypes[0]);
        }

        return [
            'keyTypes' => $keyTypes,
            'valueTypes' => $valueTypes,
        ];
    }

    private function extractTypesFromClassStringType(string $classString): ?string
    {
        preg_match('/^class-string<([^>]+)>$/', $classString, $matches);

        return $matches[1];
    }

    /**
     * @return string[]
     */
    private function extractTypesFromUnionType(string $type): array
    {
        return array_map('trim', explode('|', $type));
    }
}
