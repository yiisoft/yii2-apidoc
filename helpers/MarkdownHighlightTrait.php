<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use DomainException;
use Highlight\Highlighter;

/**
 * MarkdownHighlightTrait provides code highlighting functionality for Markdown Parsers.
 *
 * @since 2.1.1
 * @author Carsten Brandt <mail@cebe.cc>
 */
trait MarkdownHighlightTrait
{
    /**
     * @var Highlighter
     */
    private static $highlighter;


    /**
     * @inheritdoc
     */
    protected function renderCode($block)
    {
        if (self::$highlighter === null) {
            self::$highlighter = new Highlighter();
            self::$highlighter->setAutodetectLanguages([
                'apache', 'nginx',
                'bash', 'dockerfile', 'http',
                'css', 'less', 'scss',
                'javascript', 'json', 'markdown',
                'php', 'sql', 'twig', 'xml',
            ]);
        }
        try {
            if (isset($block['language'])) {
                if ($block['language'] === 'php' && strpos($block['content'], '<?=') !== false) {
                    $block['language'] = 'html';
                }

                $result = self::$highlighter->highlight($block['language'], $block['content'] . "\n");
                return "<pre><code class=\"hljs {$result->language} language-{$block['language']}\">{$result->value}</code></pre>\n";
            } else {
                $result = self::$highlighter->highlightAuto($block['content'] . "\n");
                return "<pre><code class=\"hljs {$result->language}\">{$result->value}</code></pre>\n";
            }
        } catch (DomainException $e) {
            return parent::renderCode($block);
        }
    }

    /**
     * Highlights code
     *
     * @param string $code code to highlight
     * @param string $language language of the code to highlight
     * @return string HTML of highlighted code
     */
    public static function highlight($code, $language)
    {
        if ($language !== 'php') {
            return htmlspecialchars($code, ENT_NOQUOTES | ENT_SUBSTITUTE);
        }

        if (strncmp($code, '<?php', 5) === 0) {
            $text = @highlight_string(trim($code), true);
        } else {
            $text = highlight_string("<?php " . trim($code), true);
            $text = str_replace('&lt;?php', '', $text);
            if (($pos = strpos($text, '&nbsp;')) !== false) {
                $text = substr($text, 0, $pos) . substr($text, $pos + 6);
            }
        }

        // Remove prefixes and suffixes added by php
        if (PHP_VERSION_ID >= 80300) {
            // The `highlight_string` result format has changed since PHP8.3
            $text = substr($text, 34, -13);
        } else {
            $text = substr($text, 36, -16);
        }

        return $text;
    }
}
