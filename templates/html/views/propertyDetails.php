<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/**
 * @var \yii\web\View $this
 * @var ClassDoc|TraitDoc $type
 */

/** @var \yii\apidoc\templates\html\ApiRenderer $renderer */
$renderer = $this->context;

$properties = $type->getNativeProperties();
if (empty($properties)) {
    return;
}

ArrayHelper::multisort($properties, 'name');
?>

<h2>Property Details</h2>

<div class="property-doc toggle-target-container">
    <p><a href="#" class="toggle">Hide inherited properties</a></p>

    <?php foreach ($properties as $property) { ?>
        <div id="<?= $property->name . '-detail' ?>">
            <div class="detail-header h3">
                <a href="#" class="tool-link" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a>
                <?= $renderer->createSubjectLink($property, '<span class="glyphicon icon-hash"></span>', [
                    'title' => 'direct link to this method',
                    'class' => 'tool-link hash',
                ]) ?>

                <?php if (
                    ($sourceUrl = $renderer->getSourceUrl($property->definedBy, $property->startLine)) !== null
                ) { ?>
                    <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link"
                       title="edit on github">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                <?php } ?>

                <?= $property->name ?>

                <span class="detail-header-tag small">
                    <?= $property->visibility ?>
                    <?= $property->isStatic ? 'static' : '' ?>
                    <?= $property->getIsReadOnly() ? '<em>read-only</em> ' : '' ?>
                    <?= $property->getIsWriteOnly() ? '<em>write-only</em> ' : ''?>
                    property
                    <?= !empty($property->since) ? "(available since version $property->since)" : '' ?>
                </span>
            </div>

            <?php if (!empty($property->deprecatedSince) || !empty($property->deprecatedReason)) { ?>
                <div class="doc-description deprecated">
                    <strong>
                        Deprecated

                        <?php
                        if (!empty($property->deprecatedSince))  {
                            echo 'since version ' . $property->deprecatedSince . ': ';
                        }

                        if (!empty($property->deprecatedReason)) {
                            echo ApiMarkdown::process($property->deprecatedReason, $property->definedBy, true);
                        }
                        ?>
                    </strong>
                </div>
            <?php } ?>

            <div class="doc-description">
                <?php if ($type->name !== $property->definedBy) { ?>
                    <p>
                        <strong>Defined in:</strong>
                        <?= $renderer->createSubjectLink($property, $property->fullName) ?>
                    </p>
                <?php } ?>

                <?php if ($property->shortDescription) : ?>
                    <p><strong><?= ApiMarkdown::process($property->shortDescription, $property->definedBy, true) ?></strong></p>
                    <?= ApiMarkdown::process($property->description, $property->definedBy) ?>
                <?php endif; ?>
                <?= $this->render('seeAlso', ['object' => $property]) ?>
            </div>

            <div class="signature"><?= $renderer->renderPropertySignature($property, $type); ?></div>
            <?= $this->render('@yii/apidoc/templates/html/views/changelog', ['doc' => $property]) ?>
            <?= $this->render('@yii/apidoc/templates/html/views/todos', ['doc' => $property]) ?>
        </div>
    <?php } ?>
</div>
