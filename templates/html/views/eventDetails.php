<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $type ClassDoc */

/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */
$renderer = $this->context;

$events = $type->getNativeEvents();
if (empty($events)) {
    return;
}

ArrayHelper::multisort($events, 'name');
?>

<h2>Event Details</h2>

<div class="event-doc toggle-target-container">
    <p><a href="#" class="toggle">Hide inherited properties</a></p>

    <?php foreach ($events as $event) { ?>
        <div id="<?= $event->name . '-detail' ?>">
            <div class="detail-header h3">
                <a href="#" class="tool-link" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a>
                <?= $renderer->createSubjectLink($event, '<span class="glyphicon icon-hash"></span>', [
                    'title' => 'direct link to this method',
                    'class' => 'tool-link hash',
                ]) ?>

                <?php if (($sourceUrl = $renderer->getSourceUrl($event->definedBy, $event->startLine)) !== null) { ?>
                    <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link"
                       title="edit on github">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                <?php } ?>

                <?= $event->name ?>

                <span class="detail-header-tag small">
                    event of type
                    <?= $renderer->createTypeLink($event->types) ?>
                    <?= !empty($event->since) ? "(available since version $event->since)" : '' ?>
                </span>
            </div>

            <?php if (!empty($event->deprecatedSince) || !empty($event->deprecatedReason)) { ?>
                <div class="doc-description deprecated">
                    <strong>
                        Deprecated

                        <?php
                        if (!empty($event->deprecatedSince)) {
                            echo 'since version ' . $event->deprecatedSince . ': ';
                        }

                        if (!empty($event->deprecatedReason)) {
                            echo ApiMarkdown::process($event->deprecatedReason, $event->definedBy, true);
                        }
                        ?>
                    </strong>
                </div>
            <?php } ?>

            <div class="doc-description">
                <?php if ($type->name !== $event->definedBy) { ?>
                    <p>
                        <strong>Defined in:</strong>
                        <?= $renderer->createSubjectLink($event, $event->fullName) ?>
                    </p>
                <?php } ?>

                <?= ApiMarkdown::process($event->description, $type) ?>
                <?= $this->render('seeAlso', ['object' => $event]) ?>
            </div>

            <?= $this->render('@yii/apidoc/templates/html/views/changelog', ['doc' => $event]) ?>
        </div>
    <?php } ?>
</div>
