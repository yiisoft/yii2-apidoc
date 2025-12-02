<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\Php\Trait_;

/**
 * Represents API documentation information for a `trait`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class TraitDoc extends TypeDoc
{
    // classes using the trait
    // will be set by Context::updateReferences()
    public $usedBy = [];
    public $traits = [];

    /**
     * @param Trait_|null $reflector
     * @param Context|null $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        foreach ($reflector->getUsedTraits() as $trait) {
            $this->traits[] = ltrim($trait, '\\');
        }
    }
}
