<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\helpers;

use PHPUnit\Framework\TestCase;
use yii\apidoc\helpers\TextHelper;

class TextHelperTest extends TestCase
{
    /**
     * @dataProvider provideExtractFirstSentenceData
     */
    public function testExtractFirstSentence(string $text, string $expectedResult)
    {
        $result = TextHelper::extractFirstSentence($text);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideExtractFirstSentenceData(): array
    {
        return [
            'basic' => [
                'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem '
                    . 'Ipsum has been the industry\'s standard dummy text ever since the 1500s, when '
                    . 'an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            'empty' => [
                '',
                '',
            ],
            'only spaces' => [
                '     ',
                '',
            ],
            'with backticks' => [
                'fallback host info (e.g. `https://www.yiiframework.com`) used when '
                    . '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] is invalid. This value will replace '
                    . '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] before [[$denyCallback]] is called to make sure that '
                    . 'an invalid host will not be used for further processing. You can set it to `null` to leave '
                    . '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] untouched. Default value is empty string (this will '
                    . 'result creating relative URLs instead of absolute).',
                'fallback host info (e.g. `https://www.yiiframework.com`) used when '
                    . '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] is invalid.',
            ],
            'with newline and no space after dot' => [
                "a URI [RFC3986](https://tools.ietf.org/html/rfc3986) or\n"
                    . 'URI template [RFC6570](https://tools.ietf.org/html/rfc6570). This property is required.',
                'a URI [RFC3986](https://tools.ietf.org/html/rfc3986) or'
                    . ' URI template [RFC6570](https://tools.ietf.org/html/rfc6570).',
            ],
            'abbreviation i.e.' => [
                'This works only in some cases (i.e. when cache is disabled). Other cases are ignored.',
                'This works only in some cases (i.e. when cache is disabled).',
            ],
            'abbreviation etc.' => [
                'Supported formats are JSON, XML, YAML, etc. This list may grow.',
                'Supported formats are JSON, XML, YAML, etc.',
            ],
            'question mark' => [
                'Is this value required? It depends on the environment.',
                'Is this value required?',
            ],
            'exclamation mark' => [
                'Warning! This operation is irreversible.',
                'Warning!',
            ],
            'dot before closing bracket' => [
                'See the configuration options (e.g. cache.enabled). This option is important.',
                'See the configuration options (e.g. cache.enabled).',
            ],
            'newline after sentence' => [
                "First sentence ends here.\nSecond sentence starts here.",
                'First sentence ends here.',
            ],
            'no space after sentence end' => [
                'First sentence ends here.Second sentence starts immediately.',
                'First sentence ends here.',
            ],
            'single sentence no dot' => [
                'This text has no sentence terminator',
                'This text has no sentence terminator',
            ],
            'leading whitespace' => [
                "   First sentence with leading spaces. Second sentence.",
                'First sentence with leading spaces.',
            ],
            'sentence with numbers' => [
                'Version 1.2.3 is supported. Older versions are deprecated.',
                'Version 1.2.3 is supported.',
            ],
            'ellipsis should not end sentence' => [
                'This may take some time... Please wait.',
                'This may take some time...',
            ],
            'parentheses inside sentence' => [
                'This value (default: null) is optional. It can be omitted.',
                'This value (default: null) is optional.',
            ],
        ];
    }
}
