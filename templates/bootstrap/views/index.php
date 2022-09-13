<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\templates\bootstrap\ApiRenderer;
use yii\web\View;

/**
 * @var ClassDoc[]|InterfaceDoc[]|TraitDoc[] $types
 * @var string|null $readme
 * @var View $this
 */

/** @var ApiRenderer $renderer */
$renderer = $this->context;

if (isset($readme)) {
    echo ApiMarkdown::process($readme);
}
?>
<h1>Class Reference</h1>

<table class="summaryTable docIndex table table-bordered table-striped table-hover">
    <colgroup>
        <col class="col-class">
        <col class="col-description">
    </colgroup>
    <thead>
        <tr>
            <th>Class</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($types as $class) { ?>
            <tr>
                <td><?= $renderer->createTypeLink($class, $class, $class->name) ?></td>
                <td><?= ApiMarkdown::process($class->shortDescription, $class, true) ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
