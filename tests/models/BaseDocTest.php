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
        $initialText = 'the host info (e.g. `http://www.example.com`) that is used by [[createAbsoluteUrl()]] to ' .
            'prepend to created URLs.';
        $firstSentence = BaseDoc::extractFirstSentence($initialText);
        $this->assertEquals($initialText, $firstSentence);;
    }
}
