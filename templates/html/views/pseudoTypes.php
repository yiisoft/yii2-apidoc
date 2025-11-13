<?php

use yii\apidoc\templates\html\ApiRenderer;
use yii\web\View;

/**
 * @var View&object{context: ApiRenderer} $this
 * @var string $title
 * @var PseudoTypeDoc[] $types
 */

?>

<div class="doc-method summary toggle-target-container">
    <h2><?= $title ?></h2>

    <table class="summary-table table table-striped table-bordered table-hover">
        <colgroup>
            <col class="col-name" />
            <col class="col-value" />
        </colgroup>
        <tr>
            <th>Name</th>
            <th>Value</th>
        </tr>

        <?php foreach ($types as $type): ?>
            <tr id="<?= $type->type ?>-type-<?= $type->name ?>">
                <td><?= $this->context->createSubjectLink($type) ?></td>
                <td><?= $this->context->createTypeLink($type->value) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
