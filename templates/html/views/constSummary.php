<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $type ClassDoc */

/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */
$renderer = $this->context;

if (empty($type->constants)) {
    return;
}

$constants = $type->constants;
ArrayHelper::multisort($constants, 'name');
?>

<div class="doc-const summary toggle-target-container">
    <h2>Constants</h2>

    <p><a href="#" class="toggle">Hide inherited constants</a></p>

    <table class="summary-table table table-striped table-bordered table-hover">
        <colgroup>
            <col class="col-const" />
            <col class="col-value" />
            <col class="col-description" />
            <col class="col-defined" />
        </colgroup>
        <tr>
            <th>Constant</th>
            <th>Value</th>
            <th>Description</th>
            <th>Defined By</th>
        </tr>

        <?php foreach ($constants as $constant) { ?>
            <tr id="<?= $constant->name ?>" class="<?= $constant->definedBy != $type->name ? 'inherited' : '' ?>">
              <td id="<?= $constant->name ?>-detail"><?= $constant->name ?></td>
              <td><?= $constant->value ?></td>
              <td>
                  <?= ApiMarkdown::process($constant->shortDescription . "\n" . $constant->description,
                      $constant->definedBy,
                      true
                  ) ?>
                  <?php if (!empty($constant->deprecatedSince) || !empty($constant->deprecatedReason)) { ?>
                      <strong>
                          Deprecated

                          <?php
                          if (!empty($constant->deprecatedSince)) {
                              echo 'since version ' . $constant->deprecatedSince . ': ';
                          }

                          if (!empty($constant->deprecatedReason)) {
                              echo ApiMarkdown::process($constant->deprecatedReason, $constant->definedBy, true);
                          }
                          ?>
                      </strong>
                  <?php } ?>
              </td>
              <td><?= $renderer->createTypeLink($constant->definedBy, $type) ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
