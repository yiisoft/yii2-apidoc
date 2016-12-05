<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\GithubMarkdown;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Markdown;

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

    /**
     * @var BaseRenderer
     */
    public static $renderer;
    /**
     * @var array translation for guide block types
     * @since 2.0.5
     */
    public static $blockTranslations = [];

    protected $renderingContext;
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
            if (strpos($content, '</h1>') !== false)
                $content = str_replace('</h1>', "</h1>\n" . $toc, $content);
            else
                $content = $toc . $content;
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
        $result = parent::renderLink($block);

        // add special syntax for linking to the guide
        $result = preg_replace_callback('/href="guide:([A-z0-9-.#]+)"/i', function($match) {
            return 'href="' . static::$renderer->generateGuideUrl($match[1]) . '"';
        }, $result, 1);

        return $result;
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
     * @param string $content
     * @param TypeDoc $context
     * @param bool $paragraph
     * @return string
     */
    public static function process($content, $context = null, $paragraph = false)
    {
        if (!isset(Markdown::$flavors['api'])) {
            Markdown::$flavors['api'] = new static;
        }

        if (is_string($context)) {
            $context = static::$renderer->apiContext->getType($context);
        }
        Markdown::$flavors['api']->renderingContext = $context;

        if ($paragraph) {
            return Markdown::processParagraph($content, 'api');
        } else {
            return Markdown::process($content, 'api');
        }
    }

    /**
     * Add bootstrap classes to tables.
     * @inheritdoc
     */
    public function renderTable($block)
    {
        return str_replace('<table>', '<table class="table table-bordered table-striped">', parent::renderTable($block));
    }
}
