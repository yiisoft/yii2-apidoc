<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|TraitDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

$properties = $type->getNativeProperties();
if (empty($properties)) {
    return;
}
ArrayHelper::multisort($properties, 'name');
?>
<h2>Property Details</h2>

<div class="property-doc">
<?php foreach ($properties as $property): ?>

    <div class="detail-header h3" id="<?= $property->name.'-detail' ?>">
        <a href="#" class="tool-link" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a>
        <?= $renderer->createSubjectLink($property, '<span class="glyphicon icon-hash"></span>', [
            'title' => 'direct link to this method',
            'class' => 'tool-link hash',
        ]) ?>

        <?php if (($sourceUrl = $renderer->getSourceUrl($property->definedBy, $property->startLine)) !== null): ?>
            <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link" title="edit on github"><span class="glyphicon glyphicon-pencil"></span></a>
            <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github"><span class="glyphicon glyphicon-eye-open"></span></a>
        <?php endif; ?>

        <?= $property->name ?>
        <span class="detail-header-tag small">
            <?= $property->visibility ?>
            <?= $property->isStatic ? 'static' : '' ?>
            <?php if ($property->getIsReadOnly()) echo ' <em>read-only</em> '; ?>
            <?php if ($property->getIsWriteOnly()) echo ' <em>write-only</em> '; ?>
            property
            <?php if (!empty($property->since)): ?>
                (available since version <?= $property->since ?>)
            <?php endif; ?>
        </span>
    </div>

    <?php if (!empty($property->deprecatedSince) || !empty($property->deprecatedReason)): ?>
        <div class="doc-description deprecated">
            <strong>Deprecated <?php
                if (!empty($property->deprecatedSince))  { echo 'since version ' . $property->deprecatedSince . ': '; }
                if (!empty($property->deprecatedReason)) { echo ApiMarkdown::process($property->deprecatedReason, $property->definedBy, true); }
                ?></strong>
        </div>
    <?php endif; ?>

    <div class="doc-description">
        <?= ApiMarkdown::process($property->description, $property->definedBy) ?>

        <?= $this->render('seeAlso', ['object' => $property]) ?>
    </div>

    <div class="signature"><?php echo $renderer->renderPropertySignature($property, $type); ?></div>

    <?= $this->render('@yii/apidoc/templates/html/views/changelog', ['doc' => $property]) ?>
    <?= $this->render('@yii/apidoc/templates/html/views/todos', ['doc' => $property]) ?>

<?php endforeach; ?>
</div>
