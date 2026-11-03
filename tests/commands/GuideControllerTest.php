<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Yii;
use yii\apidoc\helpers\MarkdownContext;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\helpers\FileHelper;
use yiiunit\apidoc\support\UnexpectedFullContext;
use yiiunit\apidoc\support\controllers\GuideControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\GuideController
 */
class GuideControllerTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Creates test controller instance.
     * @return GuideControllerMock
     */
    protected function createController()
    {
        $controller = new GuideControllerMock('guide', Yii::$app);
        $controller->interactive = false;

        return $controller;
    }

    /**
     * @param string $sourceDirs
     * @param string $targetDir
     * @param array $args
     * @return string command output
     */
    protected function generateGuide($sourceDirs, $targetDir = '@runtime', array $args = [])
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', array_merge([$sourceDirs, $targetDir], $args));
    }

    // Tests :

    public function testNoFiles(): void
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/support'));

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Error: No files found to process', $output);
    }

    #[DataProvider('provideGenerateHtmlData')]
    public function testGenerateHtml(string $template): void
    {
        $output = $this->generateGuide(
            Yii::getAlias('@yiiunit/apidoc/data/guide'),
            '@runtime',
            ['template' => $template]
        );

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('generating search index...done.', $output);
        $this->assertStringContainsString('Publishing images...done.', $output);

        $outputPath = Yii::getAlias('@runtime');

        foreach (['README', 'intro', 'TOC'] as $filename) {
            $filePath = $outputPath . DIRECTORY_SEPARATOR . "guide-{$filename}.html";
            $this->assertTrue(file_exists($filePath));
            $fileContent = $this->removeDynamicContentFromHtml(file_get_contents($filePath));
            $this->assertMatchesHtmlSnapshot($fileContent);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideGenerateHtmlData(): array
    {
        return [
            'bootstrap' => ['bootstrap'],
            'project' => ['project'],
        ];
    }

    public function testGeneratePdf(): void
    {
        $output = $this->generateGuide(Yii::getAlias('@yiiunit/apidoc/data/guide'), '@runtime', ['template' => 'pdf']);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Publishing images...done.', $output);

        $outputPath = Yii::getAlias('@runtime');
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'Makefile'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'main.tex'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'guide.tex'));
        $this->assertTrue(file_exists($outputPath . DIRECTORY_SEPARATOR . 'title.tex'));
    }

    #[DataProvider('provideContextCacheData')]
    public function testGuideApiLinksUseCompactCacheWithLegacyFallback(string $cache, string $location): void
    {
        $runtime = Yii::getAlias('@runtime');
        $apiDirectory = $location === 'colocated' ? "$runtime/output" : "$runtime/api";
        FileHelper::createDirectory("$apiDirectory/cache");
        FileHelper::createDirectory("$runtime/input");
        file_put_contents("$runtime/input/README.md", "# Example\n\n[[example\\Example]]");
        $context = new Context();
        $context->classes['example\\Example'] = new ClassDoc(null, null, ['name' => 'example\\Example']);
        if ($cache === 'compact') {
            file_put_contents("$apiDirectory/cache/markdown.data", serialize(MarkdownContext::create($context)));
            file_put_contents("$apiDirectory/cache/apidoc.data", serialize(new UnexpectedFullContext()));
        } else {
            file_put_contents("$apiDirectory/cache/apidoc.data", serialize($context));
            if ($cache === 'invalid') {
                file_put_contents("$apiDirectory/cache/markdown.data", 'truncated cache');
            }
        }
        $options = ['template' => 'project'];
        if ($location !== 'colocated') {
            $options['apiDocs'] = $location === 'relative' ? '../api' : $apiDirectory;
        }

        $this->generateGuide("$runtime/input", "$runtime/output", $options);

        $html = file_get_contents("$runtime/output/guide-README.html");
        $this->assertStringContainsString('example-example.html', $html);
        $this->assertStringNotContainsString('broken-link', $html);
    }

    public static function provideContextCacheData(): array
    {
        return [
            'compact absolute path' => ['compact', 'absolute'],
            'compact relative path' => ['compact', 'relative'],
            'compact colocated' => ['compact', 'colocated'],
            'legacy cache' => ['legacy', 'absolute'],
            'invalid compact cache' => ['invalid', 'absolute'],
        ];
    }
}
