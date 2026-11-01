<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use DOMDocument;
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

    public function testPreservesEncodedExamplesWhenParsingHtml(): void
    {
        $code = <<<'CODE'
<?php
echo $ready && $enabled;
<input name="example" <?= $attributes ?>>
&lt;literal entity&gt;
CODE;
        $html = '<p>Пример café 😀 <a href="guide.md?first=1&amp;second=2">guide</a></p>'
            . '<pre><code>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</code></pre>';
        $converted = EncodingHelper::convertToUtf8WithHtmlEntities($html);
        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument();
            $this->assertTrue($document->loadHTML($converted));
            $this->assertSame([], libxml_get_errors());
            $this->assertSame($code, $document->getElementsByTagName('code')->item(0)->textContent);
            $this->assertSame('Пример café 😀 guide', $document->getElementsByTagName('p')->item(0)->textContent);
            $this->assertSame('guide.md?first=1&second=2', $document->getElementsByTagName('a')->item(0)->getAttribute('href'));
            $this->assertSame(0, $document->getElementsByTagName('input')->length);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
