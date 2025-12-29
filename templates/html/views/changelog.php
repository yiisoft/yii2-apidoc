<?php

use yii\apidoc\models\BaseDoc;

/** @var BaseDoc $doc */
?>

<?php if ($doc->sinceMap) { ?>
    <table class="changelog table table-bordered">
        <thead>
            <tr>
                <th scope="col">Version</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doc->sinceMap as $version => $description) { ?>
                <tr>
                    <th><?= $version ?></th>
                    <td><?= $description ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
