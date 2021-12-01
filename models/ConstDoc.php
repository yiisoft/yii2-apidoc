<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Php\Constant;
use yii\apidoc\helpers\PrettyPrinter;

/**
 * Represents API documentation information for a `constant`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ConstDoc extends BaseDoc
{
    public $definedBy;
    public $value;


    /**
     * @param Constant $reflector
     * @param Context $context
     * @param array $config
     * @param DocBlock $docBlock
     */
    public function __construct($reflector = null, $context = null, $config = [], $docBlock = null)
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        if ($reflector->getValue() !== null) {
            $this->value = PrettyPrinter::getRepresentationOfValue($reflector->getDefaultNode());
        }
    }
}
