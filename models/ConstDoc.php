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
 */
class ConstDoc extends BaseDoc
{
    public $definedBy;
    /**
     * @var string|null
     */
    public $value;


    /**
     * @param Constant|null $reflector
     * @param Context|null $context
     * @param array $config
     * @param DocBlock|null $docBlock
     */
    public function __construct($reflector = null, $context = null, $config = [], $docBlock = null)
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        if (PHP_VERSION_ID >= 80100) {
            $reflectorValue = $reflector->getValue(false);
            $this->value = $reflectorValue !== null ? (string) $reflectorValue : null;
        } else {
            $this->value = $reflector->getValue();
        }
    }
}
