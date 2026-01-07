<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\GithubMarkdown;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Markdown;
use yii\helpers\Url;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiMarkdown extends GithubMarkdown
{
    use ApiMarkdownTrait;
    use MarkdownHighlightTrait;

    private const INLINE_TAG_LINK = 'link';
    private const INLINE_TAG_SEE = 'see';

    private const PHP_FUNCTION_BASE_URL = 'https://www.php.net/manual/en/function.';

    /**
     * @var BaseRenderer|null
     */
    public static $renderer;
    /**
     * @var array translation for guide block types
     * @since 2.0.5
     */
    public static $blockTranslations = [];

    /**
     * @var TypeDoc|null
     */
    protected $renderingContext;
    /**
     * @var array
     */
    protected $headings = [];


    /**
     * @return array the headlines of this document
     * @since 2.0.5
     */
    public function getHeadings()
    {
        return $this->headings;
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        parent::prepare();
        $this->headings = [];
    }

    public function parse($text)
    {
        $markup = parent::parse($text);
        $markup = $this->applyToc($markup);
        return $markup;
    }

    /**
     * @since 2.0.5
     */
    protected function applyToc($content)
    {
        // generate TOC if there is more than one headline
        if (!empty($this->headings) && count($this->headings) > 1) {
            $toc = [];
            foreach ($this->headings as $heading) {
                $toc[] = '<li>' . Html::a(strip_tags($heading['title']), '#' . $heading['id']) . '</li>';
            }
            $toc = '<div class="toc"><ol>' . implode("\n", $toc) . "</ol></div>\n";

            $needle = '</h1>';
            $pos = strpos($content, $needle);
            if ($pos !== false) {
                $content = substr_replace($content, "$needle\n$toc", $pos, strlen($needle));
            } else {
                $content = $toc . $content;
            }
        }
        return $content;
    }

    /**
     * @inheritDoc
     */
    protected function renderHeadline($block)
    {
        $content = $this->renderAbsy($block['content']);
        if (preg_match('~<span id="(.*?)"></span>~', $content, $matches)) {
            $hash = $matches[1];
            $content = preg_replace('~<span id=".*?"></span>~', '', $content);
        } else {
            $hash = Inflector::slug(strip_tags($content));
        }
        $hashLink = "<span id=\"$hash\"></span><a href=\"#$hash\" class=\"hashlink\">&para;</a>";

        if ($block['level'] == 2) {
            $this->headings[] = [
                'title' => trim($content),
                'id' => $hash,
            ];
        } elseif ($block['level'] > 2) {
            if (end($this->headings)) {
                $this->headings[key($this->headings)]['sub'][] = [
                    'title' => trim($content),
                    'id' => $hash,
                ];
            }
        }

        $tag = 'h' . $block['level'];
        return "<$tag>$content $hashLink</$tag>";
    }

    /**
     * @inheritdoc
     */
    protected function renderLink($block)
    {
        $url = $block['url'];
        if (!$url) {
            static::$renderer->apiContext->errors[] = [
                'line' => null,
                'file' => null,
                'message' => 'Using empty link.',
            ];

            return parent::renderLink($block);
        }

        $linkHtml = parent::renderLink($block);
        // add special syntax for linking to the guide
        $guideLinkHtml = preg_replace_callback('/href="guide:([A-z0-9-.#]+)"/i', function ($matches) {
            return 'href="' . static::$renderer->generateGuideUrl($matches[1]) . '"';
        }, $linkHtml, 1);
        if ($guideLinkHtml !== $linkHtml) {
            return $guideLinkHtml;
        }

        if (!property_exists(static::$renderer, 'repoUrl')) {
            return $linkHtml;
        }

        $repoUrl = static::$renderer->repoUrl;
        if (!$repoUrl) {
            if (Url::isRelative($url)) {
                static::$renderer->apiContext->errors[] = [
                    'line' => null,
                    'file' => null,
                    'message' => "Using relative link ($url) but repoUrl is not set.",
                ];
            }

            return $linkHtml;
        }

        return preg_replace_callback('/href="(.+)"/i', function ($matches) use ($repoUrl) {
            return 'href="' . $repoUrl . '/' . $matches[1] . '"';
        }, $linkHtml, 1);
    }

    /**
     * @inheritdoc
     * @since 2.0.5
     */
    protected function translateBlockType($type)
    {
        $key = ucfirst($type) . ':';
        if (isset(static::$blockTranslations[$key])) {
            $translation = static::$blockTranslations[$key];
        } else {
            $translation = $key;
        }
        return "$translation ";
    }

    /**
     * Converts markdown into HTML
     *
     * @param TypeDoc|string|null $context
     */
    public static function process(?string $content, $context = null, bool $paragraph = false): string
    {
        if (!$content) {
            return '';
        }

        if (!isset(Markdown::$flavors['api'])) {
            Markdown::$flavors['api'] = new static();
        }

        if (is_string($context)) {
            $context = static::$renderer->apiContext->getType($context);
        }

        Markdown::$flavors['api']->renderingContext = $context;

        $result = self::processInlineTags($content, self::INLINE_TAG_LINK);
        $result = self::processInlineTags($result, self::INLINE_TAG_SEE);

        return $paragraph ? Markdown::processParagraph($result, 'api') : Markdown::process($result, 'api');
    }

    /**
     * Add bootstrap classes to tables.
     * @inheritdoc
     */
    public function renderTable($block)
    {
        return str_replace('<table>', '<table class="table table-bordered table-striped">', parent::renderTable($block));
    }

    private static function processInlineTags(string $content, string $tag): string
    {
        $result = preg_replace_callback(
            '/{@' . $tag . '\s*([\w\d\\\\():$]+(?:\|[^}]*)?)}/',
            function (array $matches) {
                $linkContent = $matches[1];

                if (strpos($linkContent, '()') !== false) {
                    $functionName = trim(substr($linkContent, strripos($linkContent, '\\') ?: 0, -2), '\\');
                    if (function_exists($functionName)) {
                        $functionUrl =  self::PHP_FUNCTION_BASE_URL . str_replace('_', '-', $functionName) . '.php';
                        return '[' . $functionName . '](' . $functionUrl . ')';
                    }
                }

                return '[[' . $linkContent . ']]';
            },
            $content
        );

        $result = preg_replace('/{@' . $tag . '\s+([^}]+)}/', '$1', $result);

        return $result;
    }
}
