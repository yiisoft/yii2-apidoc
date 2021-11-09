<?php

/* @var $object yii\apidoc\models\BaseDoc */
/* @var $this yii\web\View */

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\TypeDoc;

$type = $object instanceof TypeDoc ? $object : $object->definedBy;

$see = [];
foreach ($object->tags as $tag) {
    /** @var $tag phpDocumentor\Reflection\DocBlock\Tags\See */
    if (get_class($tag) == 'phpDocumentor\Reflection\DocBlock\Tags\See') {
        $ref = $tag->getReference();
        if (strpos($ref, '://') === false) {
            $ref = '[[' . $ref . ']]';
        }
        $see[] = rtrim(ApiMarkdown::process($ref . ' ' . $tag->getDescription(), $type, true), ". \r\n");
    }
}
if (empty($see)) {
    return;
} elseif (count($see) == 1) {
    echo '<p>See also ' . reset($see) . '.</p>';
} else {
    echo '<p>See also:</p><ul>';
    foreach ($see as $ref) {
        if (!empty($ref)) {
            if (substr_compare($ref, '>', -1, 1)) {
                $ref .= '.';
            }
            echo "<li>$ref</li>";
        }
    }
    echo '</ul>';
}
