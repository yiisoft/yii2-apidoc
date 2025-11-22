<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\TypeResolver;
use yii\base\BaseObject;
use yii\helpers\StringHelper;

/**
 * Base class for API documentation information.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseDoc extends BaseObject
{
    private const PHPSTAN_TYPE_ANNOTATION_NAME = 'phpstan-type';
    private const PSALM_TYPE_ANNOTATION_NAME = 'psalm-type';

    private const PHPSTAN_IMPORT_TYPE_ANNOTATION_NAME = 'phpstan-import-type';
    private const PSALM_IMPORT_TYPE_ANNOTATION_NAME = 'psalm-import-type';

    private const INHERITDOC_TAG_NAME = 'inheritdoc';

    /**
     * @var \phpDocumentor\Reflection\Types\Context
     */
    public $phpDocContext;
    /**
     * @var string|null
     */
    public $name;
    /**
     * @var string|null
     */
    public $fullName;
    public $sourceFile;
    public $startLine;
    public $endLine;
    public $shortDescription;
    public $description;
    /**
     * @var string|null Available since this version.
     */
    public $since;
    /**
     * @var array A mapping where keys are versions and values are descriptions.
     */
    public $sinceMap = [];
    public $deprecatedSince;
    public $deprecatedReason;
    /**
     * @var Tag[]
     */
    public $tags = [];
    /**
     * @var Generic[]
     */
    public $todos = [];
    /**
     * @var array<string, Template>
     */
    public $templates = [];
    /**
     * @var self|null
     */
    public $parent = null;
    /**
     * @var array<string, PseudoTypeDoc>
     */
    public array $phpStanTypes = [];
    /**
     * @var array<string, PseudoTypeDoc>
     */
    public array $psalmTypes = [];
    /**
     * @var array<string, PseudoTypeImportDoc>
     */
    public array $phpStanTypeImports = [];
    /**
     * @var array<string, PseudoTypeImportDoc>
     */
    public array $psalmTypeImports = [];

    /**
     * Checks if doc has tag of a given name
     * @param string $name tag name
     * @return bool if doc has tag of a given name
     */
    public function hasTag($name)
    {
        foreach ($this->tags as $tag) {
            if (strtolower($tag->getName()) == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes tag of a given name
     * @param string $name
     */
    public function removeTag($name)
    {
        foreach ($this->tags as $i => $tag) {
            if (strtolower($tag->getName()) == $name) {
                unset($this->tags[$i]);
            }
        }
    }

    /**
     * Get the first tag of a given name
     * @param string $name tag name.
     * @return Tag|null tag instance, `null` if not found.
     * @since 2.0.5
     */
    public function getFirstTag($name)
    {
        foreach ($this->tags as $i => $tag) {
            if (strtolower($tag->getName()) == $name) {
                return $this->tags[$i];
            }
        }

        return null;
    }

    /**
     * Returns the Composer package for this type, if it can be determined from [[sourceFile]].
     *
     * @return string|null
     * @since 2.1.3
     */
    public function getPackageName()
    {
        if (!$this->sourceFile || !preg_match('/\/vendor\/([\w\-]+\/[\w\-]+)/', $this->sourceFile, $match)) {
            return null;
        }

        return $match[1];
    }

    /**
     * @param self|null $parent
     * @param Class_|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($parent = null, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        $this->parent = $parent;

        if ($reflector === null) {
            return;
        }

        $fqsenResolver = new FqsenResolver();
        $typeResolver = new TypeResolver($fqsenResolver);

        // base properties
        $this->fullName = trim((string) $reflector->getFqsen(), '\\()');

        $position = strrpos($this->fullName, '::');
        $this->name = $position === false ? $this->fullName : substr($this->fullName, $position + 2);

        $this->startLine = $reflector->getLocation()->getLineNumber();
        $this->endLine = $reflector->getEndLocation()->getLineNumber();

        $docBlock = $reflector->getDocBlock();
        if ($docBlock === null) {
            if ($context !== null) {
                $context->warnings[] = [
                    'line' => $this->startLine,
                    'file' => $this->sourceFile,
                    'message' => "No docblock for element '{$this->name}'",
                ];
            }

            return;
        }

        $this->shortDescription = StringHelper::mb_ucfirst($docBlock->getSummary());
        if (empty($this->shortDescription) && !($this instanceof PropertyDoc) && $context !== null && $docBlock->getTagsByName('inheritdoc') === null) {
            $context->warnings[] = [
                'line' => $this->startLine,
                'file' => $this->sourceFile,
                'message' => "No short description for " . substr(StringHelper::basename(get_class($this)), 0, -3) . " '{$this->name}'",
            ];
        }
        $this->shortDescription = static::convertInlineLinks($this->shortDescription);

        $this->description = $docBlock->getDescription()->render();
        $this->description = static::convertInlineLinks($this->description);

        $this->phpDocContext = $docBlock->getContext();

        $this->tags = $docBlock->getTags();
        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof Since) {
                $description = (string) $tag->getDescription();
                if (!$this->since && !$this->sinceMap && !$description) {
                    $this->since = $tag->getVersion();
                }

                if ($description) {
                    $this->sinceMap[$tag->getVersion()] = $description;
                }

                unset($this->tags[$i]);
            } elseif ($tag instanceof Deprecated) {
                $this->deprecatedSince = $tag->getVersion();
                $this->deprecatedReason = (string) $tag->getDescription();
                unset($this->tags[$i]);
            } elseif ($tag instanceof Template) {
                $this->templates[(string) $fqsenResolver->resolve($tag->getTemplateName(), $this->phpDocContext)] = $tag;
                unset($this->tags[$i]);
            } elseif ($tag->getName() === 'todo') {
                $this->todos[] = $tag;
                unset($this->tags[$i]);
            } elseif ($tag instanceof Generic) {
                if ($tag->getName() === self::PHPSTAN_TYPE_ANNOTATION_NAME) {
                    $tagData = explode(' ', trim($tag->getDescription()), 2);
                    $phpStanType = new PseudoTypeDoc(
                        PseudoTypeDoc::TYPE_PHPSTAN,
                        $this,
                        trim($tagData[0]),
                        $typeResolver->resolve(trim($tagData[1]), $this->phpDocContext)
                    );
                    $this->phpStanTypes[(string) $fqsenResolver->resolve($phpStanType->name, $this->phpDocContext)] = $phpStanType;
                    unset($this->tags[$i]);
                } elseif ($tag->getName() === self::PSALM_TYPE_ANNOTATION_NAME) {
                    $tagData = explode('=', trim($tag->getDescription()), 2);
                    $psalmType = new PseudoTypeDoc(
                        PseudoTypeDoc::TYPE_PSALM,
                        $this,
                        trim($tagData[0]),
                        $typeResolver->resolve(trim($tagData[1]), $this->phpDocContext)
                    );
                    $this->psalmTypes[(string) $fqsenResolver->resolve($psalmType->name, $this->phpDocContext)] = $psalmType;
                    unset($this->tags[$i]);
                } elseif ($tag->getName() === self::PHPSTAN_IMPORT_TYPE_ANNOTATION_NAME) {
                    $tagData = explode(' from ', trim($tag->getDescription()), 2);
                    $phpStanTypeImport = new PseudoTypeImportDoc(
                        PseudoTypeImportDoc::TYPE_PHPSTAN,
                        trim($tagData[0]),
                        $fqsenResolver->resolve(trim($tagData[1]), $this->phpDocContext)
                    );
                    $this->phpStanTypeImports[(string) $fqsenResolver->resolve($phpStanTypeImport->typeName, $this->phpDocContext)] = $phpStanTypeImport;
                    unset($this->tags[$i]);
                } elseif ($tag->getName() === self::PSALM_IMPORT_TYPE_ANNOTATION_NAME) {
                    $tagData = explode(' from ', trim($tag->getDescription()), 2);
                    $psalmTypeImport = new PseudoTypeImportDoc(
                        PseudoTypeImportDoc::TYPE_PSALM,
                        trim($tagData[0]),
                        $fqsenResolver->resolve(trim($tagData[1]), $this->phpDocContext)
                    );
                    $this->psalmTypeImports[(string) $fqsenResolver->resolve($psalmTypeImport->typeName, $this->phpDocContext)] = $psalmTypeImport;
                    unset($this->tags[$i]);
                }
            }
        }

        if (in_array($this->shortDescription, ['{@inheritdoc}', '{@inheritDoc}', '@inheritdoc', '@inheritDoc'], true)) {
            // Mock up parsing of '{@inheritdoc}' (in brackets) tag, which is not yet supported at "phpdocumentor/reflection-docblock" 2.x
            // todo consider removal in case of "phpdocumentor/reflection-docblock" upgrade
            $this->tags[] = new Generic(self::INHERITDOC_TAG_NAME);
            $this->shortDescription = '';
        }
    }

    protected function isInheritdocTag(Tag $tag): bool
    {
        return $tag instanceof Generic && $tag->getName() === self::INHERITDOC_TAG_NAME;
    }

    /**
     * Converts inline links to unified format.
     * @see ApiMarkdownTrait::parseApiLinks()
     * @param string|null $content
     * @return string|null
     */
    protected static function convertInlineLinks($content)
    {
        if (!$content) {
            return $content;
        }

        return preg_replace('/{@link\s*([\w\d\\\\():$]+(?:\|[^}]*)?)}/', "[[$1]]", $content);
    }

    /**
     * Extracts first sentence out of text.
     *
     * @param string $text
     * @param string $prevText
     * @return string
     */
    public static function extractFirstSentence($text, $prevText = '')
    {
        $text = str_replace(["\r\n", "\n"], ' ', $text);
        $length = mb_strlen($text, 'utf-8');
        if ($length > 4 && ($pos = mb_strpos($text, '. ', 4, 'utf-8')) !== false) {
            $sentence = mb_substr($text, 0, $pos + 1, 'utf-8');
            $prevText  .= $sentence;

            if ($length >= $pos + 2) {
                $abbrev = mb_substr($text, $pos - 3, 4, 'utf-8');
                // do not break sentence after abbreviation
                if (
                    $abbrev === 'e.g.' ||
                    $abbrev === 'i.e.' ||
                    mb_substr_count($prevText, '`', 'utf-8') % 2 === 1
                ) {
                    $sentence .= static::extractFirstSentence(
                        mb_substr($text, $pos + 1, $length, 'utf-8'),
                        $prevText
                    );
                }
            }
            return $sentence;
        }

        return $text;
    }

    /**
     * Multibyte version of ucfirst()
     * @deprecated Use \yii\helpers\StringHelper::mb_ucfirst() instead
     * @since 2.0.6
     */
    protected static function mbUcFirst($string)
    {
        $firstChar = mb_strtoupper(mb_substr($string, 0, 1, 'utf-8'), 'utf-8');
        return $firstChar . mb_substr($string, 1, mb_strlen($string, 'utf-8'), 'utf-8');
    }
}
