<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\DocBlock\Type\Collection;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\TypeDoc;

/**
 * Class ApiMarkdownTrait
 *
 * @property TypeDoc $renderingContext
 */
trait ApiMarkdownTrait
{
    /**
     * @marker [[
     */
    protected function parseApiLinks($text)
    {
        if (!preg_match('/^\[\[([\w\d\\\\\(\):$]+)(\|[^\]]*)?\]\]/', $text, $matches)) {
            return [['text', '[['], 2];
        }

        $offset = strlen($matches[0]);
        $object = $matches[1];
        $title = (empty($matches[2]) || $matches[2] == '|') ? null : substr($matches[2], 1);

        /** @var TypeDoc[] $contexts */
        $this->_findContexts($this->renderingContext, $contexts);
        $contexts = array_unique($contexts, SORT_REGULAR);
        $contexts[] = null;

        $e = null;
        foreach ($contexts as $context) {
            /** @var TypeDoc|null $context */
            try {
                return $this->parseApiLinkForContext($offset, $object, $title, $context);
            } catch (BrokenLinkException $e) {
                // Keep going if there are more contexts to check
                continue;
            }
        }

        // If we made it this far, there was a broken link
        /** @var BrokenLinkException $e */
        static::$renderer->apiContext->errors[] = [
            'file' => ($e->context !== null) ? $e->context->sourceFile : null,
            'message' => $e->getMessage(),
        ];

        return [
            ['brokenApiLink', '<span class="broken-link">' . $object . '</span>'],
            $offset
        ];
    }

    /**
     * @param TypeDoc $type
     * @param array $contexts
     * @since 2.1.3
     */
    private function _findContexts($type, &$contexts = array())
    {
        if ($type === null) {
            return;
        }

        $contexts[] = $type;

        if ($type instanceof ClassDoc) {
            foreach ($type->traits as $trait) {
                $this->_findContexts(static::$renderer->apiContext->getType($trait), $contexts);
            }
            foreach ($type->interfaces as $interface) {
                $this->_findContexts(static::$renderer->apiContext->getType($interface), $contexts);
            }
            if ($type->parentClass) {
                $this->_findContexts(static::$renderer->apiContext->getType($type->parentClass), $contexts);
            }
        } elseif ($type instanceof InterfaceDoc) {
            foreach ($type->parentInterfaces as $interface) {
                $this->_findContexts(static::$renderer->apiContext->getType($interface), $contexts);
            }
        }
    }

    /**
     * Attempts to parse an API link for the given context.
     *
     * @param int $offset
     * @param string $object
     * @param string|null $title
     * @param TypeDoc|null $context
     * @return array
     * @throws BrokenLinkException if the object can't be resolved
     */
    protected function parseApiLinkForContext($offset, $object, $title, $context)
    {
        if (($pos = strpos($object, '::')) !== false) {
            $typeName = substr($object, 0, $pos);
            $subjectName = substr($object, $pos + 2);

            if ($context !== null) {
                // Collection resolves relative types
                $typeName = (new Collection([$typeName], $context->phpDocContext))->__toString();
            }

            /** @var $type TypeDoc */
            $type = static::$renderer->apiContext->getType($typeName);

            if ($type === null || $subjectName === '') {
                throw new BrokenLinkException($typeName . '::' . $subjectName, $context);
            }
            if (($subject = $type->findSubject($subjectName)) === null) {
                throw new BrokenLinkException($type->name . '::' . $subjectName, $context);
            }

            if ($title === null) {
                $title = $type->name . '::' . $subject->name;
                if ($subject instanceof MethodDoc) {
                    $title .= '()';
                }
            }

            return [
                ['apiLink', static::$renderer->createSubjectLink($subject, $title)],
                $offset
            ];
        }

        if ($context !== null) {
            if (($subject = $context->findSubject($object)) !== null) {
                return [
                    ['apiLink', static::$renderer->createSubjectLink($subject, $title)],
                    $offset
                ];
            }

            // Collection resolves relative types
            $object = (new Collection([$object], $context->phpDocContext))->__toString();
        }

        if (($type = static::$renderer->apiContext->getType($object)) !== null) {
            return [
                ['apiLink', static::$renderer->createTypeLink($type, null, $title)],
                $offset
            ];
        }

        if (strpos($typeLink = static::$renderer->createTypeLink($object, null, $title), '<a href') !== false) {
            return [
                ['apiLink', $typeLink],
                $offset
            ];
        }

        throw new BrokenLinkException($object, $context);
    }

    /**
     * Renders API link
     * @param array $block
     * @return string
     */
    protected function renderApiLink($block)
    {
        return $block[1];
    }

    /**
     * Renders API link that is broken i.e. points nowhere
     * @param array $block
     * @return string
     */
    protected function renderBrokenApiLink($block)
    {
        return $block[1];
    }

    /**
     * Consume lines for a blockquote element
     */
    protected function consumeQuote($lines, $current)
    {
        $block = parent::consumeQuote($lines, $current);

        $blockTypes = [
            'warning',
            'note',
            'info',
            'tip',
        ];

        // check whether this is a special Info, Note, Warning, Tip block
        $content = $block[0]['content'];
        $first = reset($content);
        if (isset($first[0]) && $first[0] === 'paragraph') {
            $parfirst = reset($first['content']);
            if (isset($parfirst[0]) && $parfirst[0] === 'text') {
                foreach ($blockTypes as $type) {
                    if (strncasecmp("$type: ", $parfirst[1], $len = strlen($type) + 2) === 0) {
                        // remove block indicator
                        $block[0]['content'][0]['content'][0][1] = substr($parfirst[1], $len);
                        // add translated block indicator as bold text
                        array_unshift($block[0]['content'][0]['content'], [
                            'strong',
                            [
                                ['text', $this->translateBlockType($type)],
                            ],
                        ]);
                        $block[0]['blocktype'] = $type;
                        break;
                    }
                }
            }
        }
        return $block;
    }

    /**
     * @since 2.0.5
     */
    protected abstract function translateBlockType($type);

    /**
     * Renders a blockquote
     */
    protected function renderQuote($block)
    {
        $class = '';
        if (isset($block['blocktype'])) {
            $class = ' class="' . $block['blocktype'] . '"';
        }
        return "<blockquote{$class}>" . $this->renderAbsy($block['content']) . "</blockquote>\n";
    }
}
