<?php

/**
 * @var \yii\apidoc\models\BaseDoc $object
 * @var \yii\web\View $this
 */

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\TypeDoc;

if ($object instanceof TypeDoc) {
    $type = $object;
} elseif (property_exists($object, 'definedBy')) {
    $type = $object->definedBy;
} else {
    $type = null;
}

$see = [];
foreach ($object->tags as $tag) {
    /** @var \phpDocumentor\Reflection\DocBlock\Tags\See $tag */
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
