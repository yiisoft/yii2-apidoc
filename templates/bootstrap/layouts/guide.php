<?php

use yii\apidoc\templates\bootstrap\ApiRenderer;
use yii\apidoc\templates\bootstrap\SideNavWidget;

/**
 * @var \yii\web\View $this
 * @var string $content
 * @var array $chapters
 */

/** @var ApiRenderer $renderer */
$renderer = $this->context;

if (isset($currentFile)) {
    foreach ($chapters as $chapter) {
        foreach ($chapter['content'] as $chContent) {
            if ($chContent['file'] == basename($currentFile)) {
                $guideHeadline = "{$chContent['headline']} - {$chapter['headline']}";
            }
        }
    }
}

$this->beginContent('@yii/apidoc/templates/bootstrap/layouts/main.php', isset($guideHeadline) ? ['guideHeadline' => $guideHeadline] : []);
?>

<div class="row">
    <div class="col-md-2">
        <?php
        $nav = [];
        foreach ($chapters as $chapter) {
            $items = [];
            foreach($chapter['content'] as $chContent) {
                $items[] = [
                    'label' => $chContent['headline'],
                    'url' => $renderer->generateGuideUrl($chContent['file']),
                    'active' => isset($currentFile) && ($chContent['file'] == basename($currentFile)),
                ];
            }
            $nav[] = [
                'label' => $chapter['headline'],
                //'url' => $renderer->generateGuideUrl($file),
                'items' => $items,
            ];
        } ?>
        <?= SideNavWidget::widget([
            'id' => 'navigation',
            'items' => $nav,
            'view' => $this,
        ]) ?>
    </div>
    <div class="col-md-9 guide-content" role="main">
        <?= $content ?>
        <div class="toplink"><a href="#" class="h1" title="go to top"><span class="glyphicon glyphicon-arrow-up"></a></div>
    </div>
</div>

<?php $this->endContent(); ?>
