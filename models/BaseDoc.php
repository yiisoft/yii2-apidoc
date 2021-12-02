<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Factory\Type;
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
    /**
     * @var \phpDocumentor\Reflection\Types\Context
     */
    public $phpDocContext;
    public $name;
    public $fullName;
    public $sourceFile;
    public $startLine;
    public $endLine;
    public $shortDescription;
    public $description;
    public $since;
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
     * @param Type|null $aggregatedType
     * @return string[]
     */
    protected function splitTypes($aggregatedType)
    {
        if ($aggregatedType === null) {
            return [];
        }

        $types = [];
        foreach ($aggregatedType as $type) {
            $types[] = (string) $type;
        }

        return $types ?: [(string) $aggregatedType];
    }

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
     * @param Class_ $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        if ($reflector === null) {
            return;
        }

        // base properties
        $this->fullName = trim((string) $reflector->getFqsen(), '\\()');

        $position = strrpos($this->fullName, '::');
        $this->name = $position === false ? $this->fullName : substr($this->fullName, $position + 2);

        $this->startLine = $reflector->getLocation()->getLineNumber();

        if (method_exists($reflector, 'getNode') && $reflector->getNode()) {
            $this->endLine = $reflector->getNode()->getAttribute('endLine');
        }

        $docblock = $reflector->getDocBlock();
        if ($docblock !== null) {
            $this->shortDescription = StringHelper::mb_ucfirst($docblock->getSummary());
            if (empty($this->shortDescription) && !($this instanceof PropertyDoc) && $context !== null && $docblock->getTagsByName('inheritdoc') === null) {
                $context->warnings[] = [
                    'line' => $this->startLine,
                    'file' => $this->sourceFile,
                    'message' => "No short description for " . substr(StringHelper::basename(get_class($this)), 0, -3) . " '{$this->name}'",
                ];
            }
            $this->description = $docblock->getDescription()->render();

            $this->phpDocContext = $docblock->getContext();

            $this->tags = $docblock->getTags();
            foreach ($this->tags as $i => $tag) {
                if ($tag instanceof Since) {
                    $this->since = $tag->getVersion();
                    unset($this->tags[$i]);
                } elseif ($tag instanceof Deprecated) {
                    $this->deprecatedSince = $tag->getVersion();
                    $this->deprecatedReason = $tag->getDescription();
                    unset($this->tags[$i]);
                } elseif ($tag->getName() === 'todo') {
                    $this->todos[] = $tag;
                    unset($this->tags[$i]);
                }
            }

            if (in_array($this->shortDescription, ['{@inheritdoc}', '{@inheritDoc}', '@inheritdoc', '@inheritDoc'], true)) {
                // Mock up parsing of '{@inheritdoc}' (in brackets) tag, which is not yet supported at "phpdocumentor/reflection-docblock" 2.x
                // todo consider removal in case of "phpdocumentor/reflection-docblock" upgrade
                $this->tags[] = new Generic('inheritdoc');
                $this->shortDescription = '';
            }

        } elseif ($context !== null) {
            $context->warnings[] = [
                'line' => $this->startLine,
                'file' => $this->sourceFile,
                'message' => "No docblock for element '{$this->name}'",
            ];
        }
    }

    /**
     * Extracts first sentence out of text
     * @param string $text
     * @return string
     */
    public static function extractFirstSentence($text)
    {
        if (mb_strlen($text, 'utf-8') > 4 && ($pos = mb_strpos($text, '.', 4, 'utf-8')) !== false) {
            $sentence = mb_substr($text, 0, $pos + 1, 'utf-8');
            if (mb_strlen($text, 'utf-8') >= $pos + 3) {
                $abbrev = mb_substr($text, $pos - 1, 4, 'utf-8');
                // do not break sentence after abbreviation
                if ($abbrev === 'e.g.' ||
                    $abbrev === 'i.e.' ||
                    mb_substr_count($sentence, '`', 'utf-8') % 2 === 1 ||
                    mb_substr_count($text, '`', 'utf-8') % 2 === 1
                ) {
                    $sentence .= static::extractFirstSentence(mb_substr($text, $pos + 1, mb_strlen($text, 'utf-8'), 'utf-8'));
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
