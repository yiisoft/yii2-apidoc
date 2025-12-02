<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Interface_;

/**
 * Represents API documentation information for an `interface`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class InterfaceDoc extends TypeDoc
{
    public $parentInterfaces = [];
    /**
     * @var string[] Class names
     * @see Context::updateReferences() for initialization
     */
    public $implementedBy = [];

    /**
     * @param Interface_|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        foreach ($reflector->getParents() as $interface) {
            $this->parentInterfaces[] = ltrim($interface, '\\');
        }

        foreach ($this->methods as $method) {
            $method->isAbstract = true;
        }
    }

    protected function initProperties($reflector, $context)
    {
        // interface can not have properties
        $this->properties = [];
    }
}
