<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Php\Constant;

/**
 * Represents API documentation information for a `constant`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 *
 * @extends BaseDoc<ClassDoc|TraitDoc>
 */
class ConstDoc extends BaseDoc
{
    public $definedBy;
    public $value;


    /**
     * @param ClassDoc|TraitDoc $parent
     * @param Constant $reflector
     * @param Context $context
     * @param array $config
     * @param DocBlock $docBlock
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [], $docBlock = null)
    {
        parent::__construct($parent, $reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->value = $reflector->getValue();
    }
}
