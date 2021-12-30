<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $type ClassDoc|TraitDoc */
/* @var $highlighter \Highlight\Highlighter */

/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */
$renderer = $this->context;

$methods = $type->methods;
if (empty($methods)) {
    return;
}

ArrayHelper::multisort($methods, 'name');
?>

<h2>Method Details</h2>

<div class="method-doc toggle-target-container">
    <p><a href="#" class="toggle">Hide inherited methods</a></p>

    <?php foreach ($methods as $method) { ?>
        <div id="<?= $method->name . '()-detail' ?>"
             class="<?= $method->definedBy !== $type->name ? 'inherited': '' ?>">
            <div class="detail-header h3">
                <a href="#" class="tool-link" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a>
                <?= $renderer->createSubjectLink($method, '<span class="glyphicon icon-hash"></span>', [
                    'title' => 'direct link to this method',
                    'class' => 'tool-link hash',
                ], $type) ?>

                <?php if (($sourceUrl = $renderer->getSourceUrl($method->definedBy, $method->startLine)) !== null) { ?>
                    <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link"
                       title="edit on github">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                <?php } ?>

                <?= $method->name ?>()

                <span class="detail-header-tag small">
                    <?= $method->visibility ?>
                    <?= $method->isAbstract ? 'abstract' : '' ?>
                    <?= $method->isStatic ? 'static' : '' ?>
                    method
                    <?= !empty($method->since) ? "(available since version $method->since)" : '' ?>
                </span>
            </div>

            <?php if (!empty($method->deprecatedSince) || !empty($method->deprecatedReason)) { ?>
                <div class="doc-description deprecated">
                    <strong>
                        Deprecated

                        <?php if (!empty($method->deprecatedSince))  {
                            echo 'since version ' . $method->deprecatedSince . ': ';
                        }

                        if (!empty($method->deprecatedReason)) {
                            echo ApiMarkdown::process($method->deprecatedReason, $method->definedBy, true);
                        } ?>
                    </strong>
                </div>
            <?php } ?>

            <div class="doc-description">
                <?php if ($type->name !== $method->definedBy) { ?>
                    <p>
                        <strong>Defined in:</strong>
                        <?= $renderer->createSubjectLink($method, $method->fullName . '()') ?>
                    </p>
                <?php } ?>

                <p><strong><?= ApiMarkdown::process($method->shortDescription, $method->definedBy, true) ?></strong></p>
                <?= ApiMarkdown::process($method->description, $method->definedBy) ?>
                <?= $this->render('seeAlso', ['object' => $method]) ?>
            </div>

            <table class="detail-table table table-striped table-bordered table-hover">
                <tr><td colspan="3" class="signature"><?= $renderer->renderMethodSignature($method, $type) ?></td></tr>

                <?php if (!empty($method->params) || !empty($method->return) || !empty($method->exceptions)) { ?>
                    <?php foreach ($method->params as $param) { ?>
                        <tr>
                            <td class="param-name-col"><?= ApiMarkdown::highlight($param->name, 'php') ?></td>
                            <td class="param-type-col"><?= $renderer->createTypeLink($param->types) ?></td>
                            <td class="param-desc-col">
                               <?= ApiMarkdown::process($param->description, $method->definedBy) ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (!empty($method->return)) { ?>
                        <tr>
                            <th class="param-name-col">return</th>
                            <td class="param-type-col"><?= $renderer->createMethodReturnTypeLink($method, $type) ?></td>
                            <td class="param-desc-col">
                                <?= ApiMarkdown::process($method->return, $method->definedBy) ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php foreach ($method->exceptions as $exception => $description) { ?>
                        <tr>
                            <th class="param-name-col">throws</th>
                            <td class="param-type-col"><?= $renderer->createTypeLink($exception) ?></td>
                            <td class="param-desc-col">
                                <?= ApiMarkdown::process($description, $method->definedBy) ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>

            <?= $this->render('@yii/apidoc/templates/html/views/changelog', ['doc' => $method]) ?>
            <?= $this->render('@yii/apidoc/templates/html/views/methodSourceCode',
                ['method' => $method, 'highlighter' => $highlighter]
            ) ?>
            <?= $this->render('@yii/apidoc/templates/html/views/todos', ['doc' => $method]) ?>
        </div>
    <?php } ?>
</div>
