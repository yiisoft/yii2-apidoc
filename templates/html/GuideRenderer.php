<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use DOMDocument;
use yii\apidoc\helpers\ApiMarkdown;
use yii\helpers\Console;
use yii\apidoc\renderers\GuideRenderer as BaseGuideRenderer;
use Yii;
use yii\helpers\Html;
use yii\helpers\Markdown;
use yii\web\AssetManager;
use yii\web\View;

/**
 *
 * @property-read View $view The view instance.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class GuideRenderer extends BaseGuideRenderer
{
    public $layout;

    /**
     * @var View
     */
    private $_view;
    /**
     * @var string
     */
    private $_targetDir;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->pageTitle === null) {
            $this->pageTitle = 'The Definitive Guide to Yii 2.0';
        }
    }

    /**
     * @return View the view instance
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = new View();
            $assetPath = Yii::getAlias($this->_targetDir) . '/assets';
            if (!is_dir($assetPath)) {
                mkdir($assetPath);
            }
            $this->_view->assetManager = new AssetManager([
                'basePath' => $assetPath,
                'baseUrl' => './assets',
            ]);
        }

        return $this->_view;
    }

    /**
     * Renders a set of files given into target directory.
     *
     * @param array $files
     * @param string $targetDir
     */
    public function render($files, $targetDir)
    {
        $this->_targetDir = $targetDir;

        $fileCount = count($files) + 1;
        if ($this->controller !== null) {
            Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
        }
        $done = 0;
        $fileData = [];
        $chapters = $this->loadGuideStructure($files);
        foreach ($files as $file) {
            $fileData[$file] = file_get_contents($file);
            if (basename($file) == 'README.md') {
                continue; // to not add index file to nav
            }
        }

        foreach ($fileData as $file => $content) {
            $output = ApiMarkdown::process($content); // TODO generate links to yiiframework.com by default
            $output = $this->afterMarkdownProcess($file, $output, Markdown::$flavors['api']);
            if ($this->layout !== false) {
                $params = [
                    'chapters' => $chapters,
                    'currentFile' => $file,
                    'content' => $output,
                ];
                $output = $this->getView()->renderFile($this->layout, $params, $this);
            }
            $fileName = $this->generateGuideFileName($file);
            file_put_contents($targetDir . '/' . $fileName, $output);

            if ($this->controller !== null) {
                Console::updateProgress(++$done, $fileCount);
            }
        }
        if ($this->controller !== null) {
            Console::updateProgress(++$done, $fileCount);
            Console::endProgress(true);
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    /**
     * Callback that is called after markdown is processed.
     *
     * You may override it to do some post processing.
     * The default implementation fixes some markdown links using [[fixMarkdownLinks]] on the output.
     *
     * @param string $file the file that has been processed.
     * @param string $output the rendered HTML output.
     * @param ApiMarkdown $renderer the renderer instance.
     * @return string
     * @since 2.0.5
     */
    protected function afterMarkdownProcess($file, $output, $renderer)
    {
        return $this->fixMarkdownLinks($output);
    }

    /**
     * Given markdown file name generates resulting html file name
     * @param string $file markdown file name
     * @return string
     */
    protected function generateGuideFileName($file)
    {
        return $this->guidePrefix . basename($file, '.md') . '.html';
    }

    public function getGuideReferences()
    {
        // TODO implement for api docs
//		$refs = [];
//		foreach ($this->markDownFiles as $file) {
//			$refName = 'guide-' . basename($file, '.md');
//			$refs[$refName] = ['url' => $this->generateGuideFileName($file)];
//		}
//		return $refs;
    }

    /**
     * Adds guide name to link URLs in markdown
     * @param string $content
     * @return string
     */
    protected function fixMarkdownLinks($content)
    {
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $doc = new DOMDocument();
        $doc->loadHTML($content);

        foreach ($doc->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if (strpos($href, '.md') === false) {
                continue;
            }

            $href = $this->guidePrefix . str_replace('.md', '.html', $href);
            $link->setAttribute('href', $href);
        }

        return $doc->saveHTML();
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
        $options['href'] = $href;

        return Html::a($text, null, $options);
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
        return rtrim($this->apiUrl, '/') . '/' . strtolower(str_replace('\\', '-', $typeName)) . '.html';
    }
}
