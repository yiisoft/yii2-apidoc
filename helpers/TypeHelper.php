<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

/**
 * TODO: description
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 * @since 4.0
 */
class TypeHelper
{
    // TODO: tests
    public static function isConditionalType(string $type): bool
    {
        $cleanedType = preg_replace('/\s+/', '', $type);

        return (bool) preg_match('/^.+?\s*\?\s*.+?\s*:\s*.+?$/', $cleanedType);
    }
}
