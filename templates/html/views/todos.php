<?php

use yii\apidoc\models\BaseDoc;

/** @var BaseDoc $doc */
?>

<?php foreach ($doc->todos as $todo): ?>
    <div class="alert alert-info todo" role="alert">
        <span class="todo-label">todo</span>
        <span class="todo-description"><?= $todo->getDescription() ?></span>
    </div>
<?php endforeach; ?>
