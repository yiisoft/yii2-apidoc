<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\renderers;

use DOMDocument;
use Yii;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\apidoc\models\Context;
use yii\apidoc\templates\bootstrap\GuideRenderer;
use yii\helpers\FileHelper;
use yiiunit\apidoc\TestCase;

class HtmlGuideRendererTest extends TestCase
{
    protected function tearDown(): void
    {
        ApiMarkdown::$renderer = null;
        ApiMarkdownLaTeX::$renderer = null;
        parent::tearDown();
    }

    public function testRendersCodeExamplesAndRewritesGuideLinks(): void
    {
        $this->mockApplication();
        $directory = Yii::getAlias('@runtime');
        FileHelper::createDirectory($directory);
        $source = $directory . '/example.md';
        $code = <<<'CODE'
<?php
echo $ready && $enabled;
<input name="example" <?= $attributes ?>>
&lt;literal entity&gt;
CODE;
        file_put_contents($source, "**Пример café 😀**\n\n[next](next.md#details)\n\n```php\n" . $code . "\n```\n");
        $renderer = new GuideRenderer(['layout' => false, 'apiContext' => new Context()]);
        $previous = libxml_use_internal_errors(true);

        try {
            $renderer->render([$source], $directory);
            $document = new DOMDocument();
            $this->assertTrue($document->loadHTML(file_get_contents($directory . '/guide-example.html')));
            $this->assertSame([], libxml_get_errors());
            $this->assertSame($code . "\n", $document->getElementsByTagName('code')->item(0)->textContent);
            $this->assertSame('Пример café 😀', $document->getElementsByTagName('strong')->item(0)->textContent);
            $this->assertSame('guide-next.html#details', $document->getElementsByTagName('a')->item(0)->getAttribute('href'));
            $this->assertSame(0, $document->getElementsByTagName('input')->length);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
