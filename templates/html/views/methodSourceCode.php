<?php

use yii\apidoc\models\MethodDoc;

/* @var $method MethodDoc */
/* @var $highlighter \Highlight\Highlighter */

$sourceCode = $method->sourceCode;
?>

<?php if ($sourceCode) {
    $collapseId = 'collapse' . ucfirst($method->name); ?>

    <p>
        <a class="btn btn-link" data-toggle="collapse" href="#<?= $collapseId ?>" role="button" aria-expanded="false"
           aria-controls="<?= $collapseId ?>">
            Source code
        </a>
    </p>
    <div class="collapse" id="<?= $collapseId ?>">
        <div class="card card-body">
            <pre>
                <code class="hljs php language-php"><?= $highlighter->highlight('php', $sourceCode)->value ?></code>
            </pre>
        </div>
    </div>
<?php } ?>
