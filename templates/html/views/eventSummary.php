<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/**
 * @var \yii\web\View $this
 * @var ClassDoc $type
 */

/** @var \yii\apidoc\templates\html\ApiRenderer $renderer */
$renderer = $this->context;

if (empty($type->events)) {
    return;
}

$events = $type->events;
ArrayHelper::multisort($events, 'name');
?>

<div class="doc-event summary toggle-target-container">
    <h2>Events</h2>

    <p><a href="#" class="toggle">Hide inherited events</a></p>

    <table class="summary-table table table-striped table-bordered table-hover">
        <colgroup>
            <col class="col-event" />
            <col class="col-type" />
            <col class="col-description" />
            <col class="col-defined" />
        </colgroup>
        <tr>
            <th>Event</th>
            <th>Type</th>
            <th>Description</th>
            <th>Defined By</th>
        </tr>

        <?php foreach ($events as $event) { ?>
            <tr id="<?= $event->name ?>" class="<?= $event->definedBy !== $type->name ? 'inherited' : '' ?>">
                <td><?= $renderer->createSubjectLink($event, null, [], $type) ?></td>
                <td><?= $renderer->createTypeLink($event->types, $type) ?></td>
                <td>
                    <?= ApiMarkdown::process($event->shortDescription, $event->definedBy, true) ?>
                    <?php if (!empty($event->since)) { ?>
                        (available since version <?= $event->since ?>)
                    <?php } ?>
                </td>
                <td><?= $renderer->createTypeLink($event->definedBy, $type) ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
