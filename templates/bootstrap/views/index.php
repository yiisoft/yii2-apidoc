<?php

use yii\apidoc\helpers\ApiMarkdown;

/**
 * @var yii\apidoc\models\ClassDoc[]|yii\apidoc\models\InterfaceDoc[]|yii\apidoc\models\TraitDoc[] $types
 * @var string|null $readme
 * @var yii\web\View $this
 */

/** @var yii\apidoc\templates\bootstrap\ApiRenderer $renderer */
$renderer = $this->context;

ksort($types);

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
<?php foreach ($types as $class): ?>
        <tr>
            <td><?= $renderer->createTypeLink($class, $class, $class->name) ?></td>
            <td><?= ApiMarkdown::process($class->shortDescription, $class, true) ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
