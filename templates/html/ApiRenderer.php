<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use Highlight\Highlighter;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\web\AssetManager;
use yii\web\View;
use Yii;

/**
 * The base class for HTML API documentation renderers.
 *
 * @property-read View $view The view instance.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends BaseApiRenderer implements ViewContextInterface
{
    /**
     * @var string path or alias of the layout file to use.
     */
    public $layout;
    /**
     * @var string path or alias of the view file to use for rendering types (classes, interfaces, traits).
     */
    public $typeView = '@yii/apidoc/templates/html/views/type.php';
    /**
     * @var string path or alias of the view file to use for rendering the index page.
     */
    public $indexView = '@yii/apidoc/templates/html/views/index.php';
    /**
     * @var string
     */
    public $allClassesUrl = 'index';
    /**
     * @var string
     */
    public $typeAvailableSinceVersionLabel = 'Available since version';

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
            $this->pageTitle = 'Yii Framework 2.0 API Documentation';
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
     * Renders a given [[Context]].
     *
     * @param Context $context the api documentation context to render.
     * @param string $targetDir
     */
    public function render($context, $targetDir)
    {
        $this->apiContext = $context;
        $this->_targetDir = $targetDir;

        $types = array_merge($context->classes, $context->interfaces, $context->traits);
        $typeCount = count($types) + 1;

        if ($this->controller !== null) {
            Console::startProgress(0, $typeCount, 'Rendering files: ', false);
        }

        $done = 0;
        $higlighter = new Highlighter;

        foreach ($types as $type) {
            $fileContent = $this->renderWithLayout($this->typeView, [
                'type' => $type,
                'types' => $types,
                'highlighter' => $higlighter,
            ]);
            file_put_contents($targetDir . '/' . $this->generateFileName($type->name), $fileContent);

            if ($this->controller !== null) {
                Console::updateProgress(++$done, $typeCount);
            }
        }

        $indexFileContent = $this->renderWithLayout($this->indexView, ['types' => $types]);
        file_put_contents($targetDir . '/index.html', $indexFileContent);

        if ($this->controller !== null) {
            Console::updateProgress(++$done, $typeCount);
            Console::endProgress(true);
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    /**
     * Renders file applying layout
     * @param string $viewFile the view name
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string
     */
    protected function renderWithLayout($viewFile, $params)
    {
        $output = $this->getView()->render($viewFile, $params, $this);
        if ($this->layout !== false) {
            $params['content'] = $output;
            return $this->getView()->renderFile($this->layout, $params, $this);
        }

        return $output;
    }

    /**
     * @param ClassDoc $class
     * @return string
     */
    public function renderInheritance($class)
    {
        $parents = [];
        $parents[] = $this->createTypeLink($class);
        while ($class->parentClass !== null) {
            if (isset($this->apiContext->classes[$class->parentClass])) {
                $class = $this->apiContext->classes[$class->parentClass];
                $parents[] = $this->createTypeLink($class);
            } else {
                $parents[] = $this->createTypeLink($class->parentClass);
                break;
            }
        }

        return implode(" &raquo;\n", $parents);
    }

    /**
     * @param array $names
     * @return string
     */
    public function renderInterfaces($names)
    {
        $interfaces = [];
        sort($names, SORT_STRING);
        foreach ($names as $interface) {
            if (isset($this->apiContext->interfaces[$interface])) {
                $interfaces[] = $this->createTypeLink($this->apiContext->interfaces[$interface]);
            } else {
                $interfaces[] = $this->createTypeLink($interface);
            }
        }

        return implode(', ', $interfaces);
    }

    /**
     * @param array $names
     * @return string
     */
    public function renderTraits($names)
    {
        $traits = [];
        sort($names, SORT_STRING);
        foreach ($names as $trait) {
            if (isset($this->apiContext->traits[$trait])) {
                $traits[] = $this->createTypeLink($this->apiContext->traits[$trait]);
            } else {
                $traits[] = $this->createTypeLink($trait);
            }
        }

        return implode(', ', $traits);
    }

    /**
     * @param array $names
     * @return string
     */
    public function renderClasses($names)
    {
        $classes = [];
        sort($names, SORT_STRING);
        foreach ($names as $class) {
            if (isset($this->apiContext->classes[$class])) {
                $classes[] = $this->createTypeLink($this->apiContext->classes[$class]);
            } else {
                $classes[] = $this->createTypeLink($class);
            }
        }

        return implode(', ', $classes);
    }

    /**
     * @param PropertyDoc $property
     * @param mixed $context
     * @return string
     */
    public function renderPropertySignature($property, $context = null)
    {
        if ($property->getter !== null || $property->setter !== null) {
            $sig = [];
            if ($property->getter !== null) {
                $sig[] = $this->renderMethodSignature($property->getter, $context);
            }
            if ($property->setter !== null) {
                $sig[] = $this->renderMethodSignature($property->setter, $context);
            }

            return implode('<br />', $sig);
        }

        $definition = [];
        $definition[] = $property->visibility;
        if ($property->isStatic) {
            $definition[] = 'static';
        }

        return '<span class="signature-defs">' . implode(' ', $definition) . '</span> '
            . '<span class="signature-type">' . $this->createTypeLink($property->types, $context) . '</span>'
            . ' ' . $this->createSubjectLink($property, $property->name) . ' '
            . ApiMarkdown::highlight('= ' . $this->renderDefaultValue($property->defaultValue), 'php');
    }

    /**
     * @param MethodDoc $method
     * @return string
     */
    public function renderMethodSignature($method, $context = null)
    {
        $params = [];
        foreach ($method->params as $param) {
            $params[] = (empty($param->typeHint) ? '' : '<span class="signature-type">' . $this->createTypeLink($param->typeHint, $context) . '</span> ')
                . ($param->isPassedByReference ? '<b>&</b>' : '')
                . ApiMarkdown::highlight(
                    $param->name
                    . ($param->isOptional ? ' = ' . $this->renderDefaultValue($param->defaultValue) : ''),
                    'php'
                );
        }

        $definition = [];
        $definition[] = $method->visibility;
        if ($method->isAbstract) {
            $definition[] = 'abstract';
        }
        if ($method->isStatic) {
            $definition[] = 'static';
        }

        return '<span class="signature-defs">' . implode(' ', $definition) . '</span> '
            . '<span class="signature-type">' . ($method->isReturnByReference ? '<b>&</b>' : '')
            . ($method->returnType === null ? 'void' : $this->createTypeLink($method->returnTypes, $context)) . '</span> '
            . '<strong>' . $this->createSubjectLink($method, $method->name) . '</strong>'
            . str_replace('  ', ' ', ' ( ' . implode(', ', $params) . ' )');
    }

    /**
     * Renders the default value.
     * @param mixed $value
     * @return string
     * @since 2.1.1
     */
    public function renderDefaultValue($value)
    {
        if ($value === null) {
            return 'null';
        }

        // special numbers which are usually used in octal or hex notation
        static $specials = [
            // file permissions
            '420' => '0644',
            '436' => '0664',
            '438' => '0666',
            '493' => '0755',
            '509' => '0775',
            '511' => '0777',
            // colors used in yii\captcha\CaptchaAction
            '2113696' => '0x2040A0',
            '16777215' => '0xFFFFFF',
        ];
        if (isset($specials[$value])) {
            return $specials[$value];
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
        return $this->generateFileName($typeName);
    }

    /**
     * Generates file name for API page for a given type
     * @param string $typeName
     * @return string
     */
    protected function generateFileName($typeName)
    {
        return strtolower(str_replace('\\', '-', $typeName)) . '.html';
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return Yii::getAlias('@yii/apidoc/templates/html/views');
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
    public function getSourceUrl($type, $line = null)
    {
        return null;
    }
}
