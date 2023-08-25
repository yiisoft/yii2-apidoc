<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\commands;

use Yii;
use yiiunit\apidoc\support\controllers\ApiControllerMock;
use yiiunit\apidoc\TestCase;

/**
 * @see yii\apidoc\commands\ApiController
 */
class ApiControllerTest extends TestCase
{
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
     * @return ApiControllerMock
     */
    protected function createController()
    {
        $controller = new ApiControllerMock('api', Yii::$app);
        $controller->interactive = false;

        return $controller;
    }

    /**
     * @param string $sourceDirs
     * @param string $targetDir
     * @param array $args
     * @return string command output
     */
    protected function generateApi($sourceDirs, $targetDir = '@runtime', array $args = [])
    {
        $controller = $this->createController();
        return $this->runControllerAction($controller, 'index', array_merge([$sourceDirs, $targetDir], $args));
    }

    // Tests :

    public function testNoFiles()
    {
        $output = $this->generateApi(Yii::getAlias('@yiiunit/apidoc/data/guide'));

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Error: No files found to process', $output);
    }

    public function testGenerateBootstrap()
    {
        $output = $this->generateApi(Yii::getAlias('@yiiunit/apidoc/data/api'), '@runtime', ['template' => 'bootstrap']);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('generating search index...done.', $output);

        $outputPath = Yii::getAlias('@runtime');

        // Class `Animal` :
        $animalFile = $outputPath . DIRECTORY_SEPARATOR . 'yiiunit-apidoc-data-api-animal-animal.html';
        $this->assertTrue(file_exists($animalFile));
        $animalContent = file_get_contents($animalFile);
        $this->assertStringContainsString('<h1>Abstract Class yiiunit\apidoc\data\api\animal\Animal</h1>', $animalContent);
        $this->assertStringContainsString('<th>Available since version</th><td>1.0</td>', $animalContent);
        $this->assertStringContainsString('Animal is a base class for animals.', $animalContent);
        $this->assertContainsWithoutIndent(
            <<<HTML
<tr id="\$name" class="">
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html#\$name-detail">\$name</a></td>
    <td><a href="https://www.php.net/language.types.string">string</a></td>
    <td>Animal name.</td>
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html">yiiunit\apidoc\data\api\animal\Animal</a></td>
</tr>
HTML
            , $animalContent
        );
        $this->assertContainsWithoutIndent(
            <<<HTML
<tr id="\$birthDate" class="">
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html#\$birthDate-detail">\$birthDate</a></td>
    <td><a href="https://www.php.net/language.types.integer">integer</a></td>
    <td>Animal birth date as a UNIX timestamp.</td>
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html">yiiunit\apidoc\data\api\animal\Animal</a></td>
</tr>
HTML
            , $animalContent
        );
        $this->assertContainsWithoutIndent(
            <<<HTML
<tr id="getAge()" class="">
<td><a href="yiiunit-apidoc-data-api-animal-animal.html#getAge()-detail">getAge()</a></td>
<td>Returns animal age in seconds.</td>
<td><a href="yiiunit-apidoc-data-api-animal-animal.html">yiiunit\apidoc\data\api\animal\Animal</a></td>
</tr>
HTML
            , $animalContent
        );
        $this->assertContainsWithoutIndent(
            <<<HTML
<tr id="render()" class="">
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html#render()-detail">render()</a></td>
    <td>Renders animal description.</td>
    <td><a href="yiiunit-apidoc-data-api-animal-animal.html">yiiunit\apidoc\data\api\animal\Animal</a></td>
</tr>
HTML
            , $animalContent
        );

        // Class `Dog` :
        $dogFile = $outputPath . DIRECTORY_SEPARATOR . 'yiiunit-apidoc-data-api-animal-dog.html';
        $this->assertTrue(file_exists($dogFile));
        $dogContent = file_get_contents($dogFile);
        $this->assertStringContainsString('<th>Available since version</th><td>1.1</td>', $dogContent);
        $this->assertStringNotContainsString('@inheritdoc', $dogContent);

        // Class `Cat` :
        $catFile = $outputPath . DIRECTORY_SEPARATOR . 'yiiunit-apidoc-data-api-animal-cat.html';
        $this->assertTrue(file_exists($catFile));
        $catContent = file_get_contents($catFile);
        $this->assertStringNotContainsString('@inheritdoc', $catContent);
    }
}
