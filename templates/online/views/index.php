<?php

use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\templates\online\ApiRenderer;

/**
 * @var ClassDoc[]|InterfaceDoc[]|TraitDoc[] $types
 * @var \yii\web\View $this
 */

/** @var ApiRenderer $renderer */
$renderer = $this->context;

ksort($types);
?>
<h1>Class Reference</h1>

<table class="summaryTable docIndex">
    <colgroup>
        <col class="col-package" />
        <col class="col-class" />
        <col class="col-description" />
    </colgroup>
    <tr>
        <th>Class</th>
        <th>Description</th>
    </tr>
<?php foreach ($types as $i => $class): ?>
    <tr>
        <td><?= $renderer->createTypeLink($class, $class, $class->name) ?></td>
        <td><?= \yii\apidoc\helpers\ApiMarkdown::process($class->shortDescription, $class, true) ?></td>
    </tr>
<?php endforeach; ?>
</table>
