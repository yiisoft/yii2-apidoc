<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

/**
 * An auxiliary class for working with encodings.
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
final class EncodingHelper
{
    public static function convertToUtf8WithHtmlEntities(string $string): string
    {
        // The solution is taken from here: https://github.com/symfony/symfony/issues/44281#issuecomment-1647665965
        return mb_encode_numericentity(
            htmlspecialchars_decode(
                htmlentities($string, ENT_NOQUOTES, 'UTF-8', false),
                ENT_NOQUOTES
            ),
            [0x80, 0x10FFFF, 0, ~0],
            'UTF-8'
        );
    }
}
