<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\latex\GithubMarkdown;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Markdown;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiMarkdownLaTeX extends GithubMarkdown
{
    use ApiMarkdownTrait;

    /**
     * @var BaseRenderer
     */
    public static $renderer;

    protected $renderingContext;


    /**
     * @inheritdoc
     */
    protected function renderApiLink($block)
    {
        // TODO allow break also on camel case
        $latex = '\texttt{';
        $latex .= str_replace(
            ['\\textbackslash', '::'],
            ['\allowbreak{}\\textbackslash', '\allowbreak{}::\allowbreak{}'],
            $this->escapeLatex(strip_tags($block[1]))
        );
        $latex .= '}';

        return $latex;
    }

    /**
     * @inheritdoc
     */
    protected function renderBrokenApiLink($block)
    {
        return $this->renderApiLink($block);
    }

    /**
     * @inheritdoc
     * @since 2.0.5
     */
    protected function translateBlockType($type)
    {
        $key = ucfirst($type) . ':';
        if (isset(ApiMarkdown::$blockTranslations[$key])) {
            $translation = ApiMarkdown::$blockTranslations[$key];
        } else {
            $translation = $key;
        }
        return "$translation ";
    }

    protected function renderHeadline($block)
    {
        foreach ($block['content'] as $i => &$item) {
            if ($item[0] === 'inlinecode') {
                unset($block['content'][$i]);
            }
        }

        return parent::renderHeadline($block);
    }

    /**
     * Renders a blockquote
     */
    protected function renderQuote($block)
    {
        return '\begin{quote}' . $this->renderAbsy($block['content']) . "\\end{quote}\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderCode($block)
    {
        $language = $block['language'] ?? 'text';
        // replace No-Break Space characters in code block, which do not render in LaTeX
        $content = preg_replace("/[\x{00a0}\x{202f}]/u", ' ', $block['content']);

        return implode("\n", [
            "\\begin{minted}{" . "$language}",
            $content,
            '\end{minted}',
            '',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function renderInlineCode($block)
    {
        // replace No-Break Space characters in code block, which do not render in LaTeX
        $content = preg_replace("/[\x{00a0}\x{202f}]/u", ' ', $block[1]);

        return '\\mintinline{text}{' . str_replace("\n", ' ', $content) . '}';
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param TypeDoc $context
     * @param bool $paragraph
     * @return string
     */
    public static function process($content, $context = null, $paragraph = false)
    {
        if (!isset(Markdown::$flavors['api-latex'])) {
            Markdown::$flavors['api-latex'] = new static;
        }

        if (is_string($context)) {
            $context = static::$renderer->apiContext->getType($context);
        }
        Markdown::$flavors['api-latex']->renderingContext = $context;

        if ($paragraph) {
            return Markdown::processParagraph($content, 'api-latex');
        } else {
            return Markdown::process($content, 'api-latex');
        }
    }
}
