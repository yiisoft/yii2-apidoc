<?php

use yii\apidoc\templates\html\ApiRenderer;
use yii\web\View;

/**
 * @var View $this
 * @var string $title
 * @var PseudoTypeDoc[] $types
 */

/** @var ApiRenderer $renderer */
$renderer = $this->context;

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
                <td><?= $renderer->createSubjectLink($type) ?></td>
                <td><?= $renderer->createTypeLink($type->value) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
