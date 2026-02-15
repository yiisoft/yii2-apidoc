<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

/**
 * An auxiliary class for working with texts.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class TextHelper
{
    /**
     * Tries to extract the first sentence from the text.
     *
     * Note: Function may not handle some abbreviations correctly.
     */
    public static function extractFirstSentence(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r\n", "\n"], ' ', $text);
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            if (!in_array($char, ['.', '!', '?'], true)) {
                continue;
            }

            $endPos = $i;
            if ($char === '.') {
                // Numbers like 1.2.3
                if ($i > 0 && $i + 1 < $length && is_numeric($text[$i - 1]) && is_numeric($text[$i + 1])) {
                    continue;
                }

                // Ellipsis
                while ($endPos + 1 < $length && mb_substr($text, $endPos + 1, 1, 'UTF-8') === '.') {
                    $endPos++;
                }
            }

            $nextIndex = $endPos + 1;
            while ($nextIndex < $length) {
                $c = mb_substr($text, $nextIndex, 1, 'UTF-8');
                if ($c === ' ' || $c === "\t") {
                    $nextIndex++;
                    continue;
                }
                break;
            }

            $nextChar = mb_substr($text, $nextIndex, 1, 'UTF-8');
            if (preg_match('/\p{Lu}/u', $nextChar)) {
                return trim(mb_substr($text, 0, $endPos + 1, 'UTF-8'));
            }

            $i = $endPos;
        }

        return $text;
    }
}
