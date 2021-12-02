<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\models;

use PHPUnit\Framework\TestCase;
use yii\apidoc\models\BaseDoc;

class BaseDocTest extends TestCase
{
    /**
     * @link https://github.com/yiisoft/yii2-apidoc/issues/128
     */
    public function testExtractFirstSentenceWithBackticks()
    {
        $initialText = 'fallback host info (e.g. `http://www.yiiframework.com`) used when ' .
            '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] is invalid. This value will replace ' .
            '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] before [[$denyCallback]] is called to make sure that ' .
            'an invalid host will not be used for further processing. You can set it to `null` to leave ' .
            '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] untouched. Default value is empty string (this will ' .
            'result creating relative URLs instead of absolute).';
        $actualFirstSentence = BaseDoc::extractFirstSentence($initialText);
        $expectedFirstSentence = 'fallback host info (e.g. `http://www.yiiframework.com`) used when ' .
            '[[\yii\web\Request::$hostInfo|Request::$hostInfo]] is invalid.';
        $this->assertEquals($expectedFirstSentence, $actualFirstSentence);
    }
}
