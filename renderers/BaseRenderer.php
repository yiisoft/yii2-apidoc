<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\PseudoTypes\Conditional;
use phpDocumentor\Reflection\PseudoTypes\ConditionalForParameter;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\IntMask;
use phpDocumentor\Reflection\PseudoTypes\IntMaskOf;
use phpDocumentor\Reflection\PseudoTypes\KeyOf;
use phpDocumentor\Reflection\PseudoTypes\List_;
use phpDocumentor\Reflection\PseudoTypes\NonEmptyList;
use phpDocumentor\Reflection\PseudoTypes\ObjectShape;
use phpDocumentor\Reflection\PseudoTypes\OffsetAccess;
use phpDocumentor\Reflection\PseudoTypes\ShapeItem;
use phpDocumentor\Reflection\PseudoTypes\ValueOf;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\InterfaceString;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\helpers\TypeHelper;
use yii\apidoc\models\BaseDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PseudoTypeDoc;
use yii\apidoc\models\PseudoTypeImportDoc;
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
    public const GUIDE_PREFIX = 'guide-';

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
     * @var array<string, string>
     */
    private const PHP_TYPE_ALIASES = [
        'true' => 'boolean',
        'false' => 'boolean',
        'bool' => 'boolean',
        'int' => 'integer',
    ];

    /**
     * @var array<string, string>
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
     * @param BaseDoc|BaseDoc[]|Type|Type[]|string|string[]|null $types
     * @param BaseDoc|null $context
     * @param string|null $title a title to be used for the link TODO check whether [[yii\...|Class]] is supported
     * @param array $options additional HTML attributes for the link.
     * @return string
     */
    public function createTypeLink(
        $types,
        ?BaseDoc $context = null,
        ?string $title = null,
        array $options = [],
        ?TypeDoc $currentTypeDoc = null
    ) {
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
            if (is_string($type)) {
                if ($type !== '') {
                    $typeDoc = $this->getTypeDocByQualifiedClassName($type, $context);
                    if ($typeDoc !== null) {
                        $links[] = $this->createTypeLink($typeDoc, $context, $title, $options);
                        continue;
                    }
                }
            } elseif ($type instanceof Type) {
                if ($type instanceof Compound) {
                    $innerTypes = TypeHelper::getTypesByAggregatedType($type);
                    $links[] = $this->createTypeLink($innerTypes, $context, $title, $options, $currentTypeDoc);
                    continue;
                }

                if ($type instanceof ConditionalForParameter || $type instanceof Conditional) {
                    $possibleTypes = TypeHelper::getPossibleTypesByConditionalType($type);
                    $links[] = $this->createTypeLink($possibleTypes, $context, $title, $options, $currentTypeDoc);
                    continue;
                }

                if ($type instanceof Intersection) {
                    $innerTypes = TypeHelper::getTypesByAggregatedType($type);
                    $innerTypesLinks = array_map(
                        fn(Type $innerType) => $this->createTypeLink($innerType, $context, $title, $options, $currentTypeDoc),
                        $innerTypes,
                    );
                    $links[] = implode('&amp;', $innerTypesLinks);
                    continue;
                }

                if ($type instanceof OffsetAccess) {
                    $typeLink = $this->createTypeLink($type->getType(), $context, $title, $options);
                    $links[] = $typeLink . '[' . $type->getOffset() . ']';
                    continue;
                }

                if ($type instanceof Array_ && substr((string) $type, -3, 3) === ')[]') {
                    $arrayTypesLinks = $this->createTypeLink($type->getValueType(), $context, $title, $options, $currentTypeDoc);
                    $links[] = "({$arrayTypesLinks})[]";
                    continue;
                }

                if ($type instanceof Array_ && substr((string) $type, -2, 2) === '[]') {
                    $valueType = $type->getValueType();
                    if ($valueType instanceof Object_ && $valueType->getFqsen() !== null) {
                        $templateType = $this->getTemplateType((string) $valueType->getFqsen(), $context);
                        if ($templateType !== null) {
                            $typeLink = $this->createTypeLink($templateType, $context, $title, $options, $currentTypeDoc);
                            $links[] = $templateType instanceof Compound ? "({$typeLink})[]" : "{$typeLink}[]";
                            continue;
                        }
                    }

                    $links[] = $this->createTypeLink($valueType, $context, $title, $options, $currentTypeDoc) . '[]';
                    continue;
                }

                if ($type instanceof ArrayShape) {
                    $itemsLinks = $this->createLinksByShapeItems($type->getItems(), $context, $title, $options, $currentTypeDoc);
                    $mainTypeLink = $this->generateLink('array', self::PHPSTAN_TYPE_BASE_URL . 'array-shapes', $options);
                    $links[] = $mainTypeLink . '{' . implode(', ', $itemsLinks) . '}';
                    continue;
                }

                if ($type instanceof ObjectShape) {
                    $itemsLinks = $this->createLinksByShapeItems($type->getItems(), $context, $title, $options, $currentTypeDoc);
                    $mainTypeLink = $this->generateLink('object', self::PHPSTAN_TYPE_BASE_URL . 'object-shapes', $options);
                    $links[] = $mainTypeLink . '{' . implode(', ', $itemsLinks) . '}';
                    continue;
                }

                if ($type instanceof This && $currentTypeDoc !== null) {
                    $links[] = $this->createTypeLink($currentTypeDoc, null, '$this', $options);
                    continue;
                }

                if ($type instanceof Static_ && !$type->getGenericTypes() && $currentTypeDoc !== null) {
                    $links[] = $this->createTypeLink($currentTypeDoc, null, null, $options);
                    continue;
                }

                if ($type instanceof Nullable) {
                    $links[] = $this->createTypeLink([$type->getActualType(), new Null_()]);
                    continue;
                }

                if (($link = $this->createLinkByTypeWithGenerics($type, $context, $title, $options, $currentTypeDoc)) !== null) {
                    $links[] = $link;
                    continue;
                }

                if ($type instanceof Object_ && $type->getFqsen() !== null) {
                    /** @var class-string */
                    $fqsen = (string) $type->getFqsen();

                    if (($typeDoc = $this->getTypeDocByQualifiedClassName($fqsen, $context)) !== null) {
                        $links[] = $this->createTypeLink($typeDoc, $context, $typeDoc->name, $options);
                        continue;
                    }

                    if (($templateType = $this->getTemplateType($fqsen, $context)) !== null) {
                        $links[] = $this->createTypeLink($templateType, $context, $title, $options, $currentTypeDoc);
                        continue;
                    }

                    if (($phpStanType = $this->getPhpStanType($fqsen, $context)) !== null) {
                        $links[] = $this->createSubjectLink($phpStanType);
                        continue;
                    }

                    if (($psalmType = $this->getPsalmType($fqsen, $context)) !== null) {
                        $links[] = $this->createSubjectLink($psalmType);
                        continue;
                    }

                    if (($phpStanTypeImport = $this->getPhpStanTypeImport($fqsen, $context)) !== null) {
                        $links[] = $this->createSubjectLink($phpStanTypeImport);
                        continue;
                    }

                    if (($psalmTypeImport = $this->getPsalmTypeImport($fqsen, $context)) !== null) {
                        $links[] = $this->createSubjectLink($psalmTypeImport);
                        continue;
                    }
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
     * creates a link to a subject
     * @param BaseDoc|PseudoTypeDoc|PseudoTypeImportDoc $subject
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

        if ($subject instanceof PseudoTypeImportDoc) {
            $typeParentFqsen = (string) $subject->typeParentFqsen;
            $href = $this->generateApiUrl(ltrim($typeParentFqsen, '\\')) . "#{$subject->type}-type-{$subject->typeName}";
            return $this->generateLink($subject->typeName, $href, $options);
        }

        if ($title === null) {
            if ($subject instanceof MethodDoc) {
                $title = $subject->name . '()';
            } else {
                $title = $subject->name;
            }
        }

        if (!$type && property_exists($subject, 'definedBy')) {
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
     * generate link markup
     * @param $text
     * @param $href
     * @param array $options additional HTML attributes for the link.
     * @return mixed
     */
    abstract protected function generateLink($text, $href, $options = []);

    /**
     * @param BaseDoc|string|null $context
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
            $type = $this->apiContext->getType((string) $context);
            if ($type !== null) {
                return $type->namespace;
            }
        }

        return '';
    }

    /**
     * @param BaseDoc|string $type
     * @param array{forcePhpStanLink?: bool, ...} $options
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
                (class_exists($type, false) || interface_exists($type, false) || trait_exists($type, false))
                && ($reflection = new \ReflectionClass($type)) && $reflection->isInternal()
            ) {
                return $this->generateLink(
                    $linkText,
                    self::PHP_CLASS_BASE_URL . strtolower(ltrim($type, '\\')),
                    $options,
                );
            }

            if ($isForcePhpStanLink) {
                $link = $this->createPhpStanTypeLink($type, $options);
                $link ??= $this->createPhpTypeLink($type, $linkText, $options);
            } else {
                $link = $this->createPhpTypeLink($type, $linkText, $options);
                $link ??= $this->createPhpStanTypeLink($type, $options);
            }

            return $link ?? $type;
        }

        $linkText = $type->name;
        if ($title !== null) {
            $linkText = $title;
            $title = null;
        }

        return $this->generateLink($linkText, $this->generateApiUrl($type->name), $options);
    }

    /**
     * @param class-string $fqsen
     */
    private function getPhpStanType(string $fqsen, ?BaseDoc $context): ?PseudoTypeDoc
    {
        if ($context === null) {
            return null;
        }

        $phpStanType = $context->phpStanTypes[$fqsen] ?? null;
        if ($phpStanType === null) {
            return $context->parent !== null ? $this->getPhpStanType($fqsen, $context->parent) : null;
        }

        return $phpStanType;
    }

    /**
     * @param class-string $fqsen
     */
    private function getPhpStanTypeImport(string $fqsen, ?BaseDoc $context): ?PseudoTypeImportDoc
    {
        if ($context === null) {
            return null;
        }

        $phpStanTypeImport = $context->phpStanTypeImports[$fqsen] ?? null;
        if ($phpStanTypeImport === null) {
            return $context->parent !== null ? $this->getPhpStanTypeImport($fqsen, $context->parent) : null;
        }

        return $phpStanTypeImport;
    }

    /**
     * @param class-string $fqsen
     */
    private function getPsalmType(string $fqsen, ?BaseDoc $context): ?PseudoTypeDoc
    {
        if ($context === null) {
            return null;
        }

        $psalmType = $context->psalmTypes[$fqsen] ?? null;
        if ($psalmType === null) {
            return $context->parent !== null ? $this->getPsalmType($fqsen, $context->parent) : null;
        }

        return $psalmType;
    }

    /**
     * @param class-string $fqsen
     */
    private function getPsalmTypeImport(string $fqsen, ?BaseDoc $context): ?PseudoTypeImportDoc
    {
        if ($context === null) {
            return null;
        }

        $psalmTypeImport = $context->psalmTypeImports[$fqsen] ?? null;
        if ($psalmTypeImport === null) {
            return $context->parent !== null ? $this->getPsalmTypeImport($fqsen, $context->parent) : null;
        }

        return $psalmTypeImport;
    }

    /**
     * @param class-string $fqsen
     */
    private function getTemplateType(string $fqsen, ?BaseDoc $context): ?Type
    {
        if ($context === null) {
            return null;
        }

        $template = $context->templates[$fqsen] ?? null;
        if ($template === null) {
            return $context->parent !== null ? $this->getTemplateType($fqsen, $context->parent) : null;
        }

        return $template->getBound();
    }

    /**
     * @param string $className
     * @return ClassDoc|InterfaceDoc|TraitDoc|null
     */
    private function getTypeDocByQualifiedClassName(string $className, ?BaseDoc $context): ?TypeDoc
    {
        $typeDoc = $this->apiContext->getType(ltrim($className, '\\'));
        if ($typeDoc !== null) {
            return $typeDoc;
        }

        return $this->apiContext->getType($this->resolveNamespace($context) . '\\' . ltrim($className, '\\'));
    }

    /**
     * @param ShapeItem[] $items
     * @return string[]
     */
    private function createLinksByShapeItems(
        array $items,
        ?BaseDoc $context,
        ?string $title,
        array $options,
        ?TypeDoc $currentTypeDoc
    ): array {
        $links = [];

        foreach ($items as $item) {
            $itemKey = $item->getKey();
            if ($itemKey !== null && $itemKey !== '') {
                $links[] = sprintf(
                    '%s%s: %s',
                    $itemKey,
                    $item->isOptional() ? '?' : '',
                    $this->createTypeLink($item->getValue(), $context, $title, $options, $currentTypeDoc),
                );
            } else {
                $links[] = $this->createTypeLink($item->getValue(), $context, $title, $options, $currentTypeDoc);
            }
        }

        return $links;
    }

    /**
     * @return string|null Link if the type has generics and null otherwise.
     */
    private function createLinkByTypeWithGenerics(
        Type $type,
        ?BaseDoc $context,
        ?string $title,
        array $options,
        ?TypeDoc $currentTypeDoc
    ): ?string {
        /**
         * @param Type[] $genericTypes
         */
        $generateLink = function (Type $mainType, array $genericTypes) use (
            $context,
            $title,
            $options,
            $currentTypeDoc
        ): string {
            $genericTypesLinks = $this->createTypeLinksByTypes($genericTypes, $context, $title, $options);
            $mainTypeLinkOptions = array_merge($options, ['forcePhpStanLink' => true]);
            $mainTypeLink = $this->createTypeLink($mainType, $context, $title, $mainTypeLinkOptions, $currentTypeDoc);
            return  "{$mainTypeLink}&lt;" . implode(', ', $genericTypesLinks) . '&gt;';
        };

        if ($type instanceof List_ && substr((string) $type, -1, 1) === '>') {
            return $generateLink(new List_(), [$type->getValueType()]);
        }

        if ($type instanceof NonEmptyList && substr((string) $type, -1, 1) === '>') {
            return $generateLink(new NonEmptyList(), [$type->getValueType()]);
        }

        if ($type instanceof Array_ && substr((string) $type, -1, 1) === '>') {
            $genericTypes = $this->getGenericTypesByListType($type);
            return $generateLink(new Array_(), $genericTypes);
        }

        if ($type instanceof ClassString && $type->getFqsen() !== null) {
            return $generateLink(new ClassString(), [new Object_($type->getFqsen())]);
        }

        if ($type instanceof InterfaceString && $type->getFqsen() !== null) {
            return $generateLink(new InterfaceString(), [new Object_($type->getFqsen())]);
        }

        if ($type instanceof Static_ && $type->getGenericTypes()) {
            return $generateLink(new Static_(), $type->getGenericTypes());
        }

        if ($type instanceof Self_ && $type->getGenericTypes()) {
            return $generateLink(new Self_(), $type->getGenericTypes());
        }

        if ($type instanceof IntegerRange) {
            $mainTypeLink = $this->createPhpStanTypeLink('int', $options);
            return $mainTypeLink . '&lt;' . $type->getMinValue() . ', ' . $type->getMaxValue() . '&gt;';
        }

        if ($type instanceof Iterable_ && substr((string) $type, -1, 1) === '>') {
            $genericTypes = $this->getGenericTypesByListType($type);
            return $generateLink(new Iterable_(), $genericTypes);
        }

        if ($type instanceof KeyOf) {
            $genericTypeLink = $this->createTypeLink($type->getType(), $context, $title, $options);
            return $this->createPhpStanTypeLink('key-of', $options) . "&lt;{$genericTypeLink}&gt;";
        }

        if ($type instanceof ValueOf) {
            $genericTypeLink = $this->createTypeLink($type->getType(), $context, $title, $options);
            return $this->createPhpStanTypeLink('value-of', $options) . "&lt;{$genericTypeLink}&gt;";
        }

        if ($type instanceof IntMask) {
            $genericTypesLinks = $this->createTypeLinksByTypes($type->getTypes(), $context, $title, $options);
            return $this->createPhpStanTypeLink('int-mask', $options) . '&lt;' . implode(', ', $genericTypesLinks) . '&gt;';
        }

        if ($type instanceof IntMaskOf) {
            $genericTypeLink = $this->createTypeLink($type->getType(), $context, $title, $options);
            return $this->createPhpStanTypeLink('int-mask-of', $options) . "&lt;{$genericTypeLink}&gt;";
        }

        if ($type instanceof Collection) {
            $genericTypes = $this->getGenericTypesByListType($type);
            return $generateLink(new Object_($type->getFqsen()), $genericTypes);
        }

        return null;
    }

    /**
     * @param Type[] $types
     */
    private function createTypeLinksByTypes(
        array $types,
        ?BaseDoc $context,
        ?string $title,
        array $options
    ): array {
        return array_map(
            fn(Type $type) => $this->createTypeLink($type, $context, $title, $options),
            $types,
        );
    }

    private function createPhpTypeLink(string $type, string $linkText, array $options): ?string
    {
        if (!in_array($type, self::PHP_TYPES)) {
            return null;
        }

        if (isset(self::PHP_TYPE_DISPLAY_ALIASES[$type])) {
            $linkText = self::PHP_TYPE_DISPLAY_ALIASES[$type];
        }

        if (isset(self::PHP_TYPE_ALIASES[$type])) {
            $type = self::PHP_TYPE_ALIASES[$type];
        }

        return $this->generateLink(
            $linkText,
            self::PHP_TYPE_BASE_URL . strtolower(ltrim($type, '\\')),
            $options,
        );
    }

    private function createPhpStanTypeLink(string $type, array $options): ?string
    {
        foreach (self::PHPSTAN_TYPES_DOC_LINKS as $phpstanDocLink => $phpstanTypes) {
            if (in_array($type, $phpstanTypes)) {
                return $this->generateLink(
                    $type,
                    self::PHPSTAN_TYPE_BASE_URL . $phpstanDocLink,
                    $options,
                );
            }
        }

        return null;
    }

    /**
     * @return array{0: Type, 1?: Type}
     */
    private function getGenericTypesByListType(AbstractList $type): array
    {
        return $type->getOriginalKeyType() !== null
            ? [$type->getOriginalKeyType(), $type->getValueType()]
            : [$type->getValueType()];
    }
}
