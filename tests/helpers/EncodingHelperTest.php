<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use yii\apidoc\helpers\EncodingHelper;
use yiiunit\apidoc\TestCase;

class EncodingHelperTest extends TestCase
{
    public function testConvertToUtf8WithHtmlEntities(): void
    {
        $string = "<div><p>tèé Заматеріалами \"&'+</p></div>";
        $result = EncodingHelper::convertToUtf8WithHtmlEntities($string);
        $expectedResult = '<div><p>t&egrave;&eacute; &#1047;&#1072;&#1084;&#1072;&#1090;&#1077;&#1088;&#1110;&#1072;&#1083;&#1072;&#1084;&#1080; "&\'+</p></div>';
        $this->assertSame($expectedResult, $result);
    }
}
