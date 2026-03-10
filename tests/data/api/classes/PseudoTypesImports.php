<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

/**
 * Description.
 *
 * @phpstan-import-type SomeData from PseudoTypes
 * @psalm-import-type OnlyPsalmType from PseudoTypes
 * @phpstan-import-type OnlyPHPStanType from PseudoTypes
 */
class PseudoTypesImports
{
    /**
     * Description.
     * @return SomeData
     */
    public function getSomeData()
    {
    }

    /**
     * Description.
     * @return OnlyPsalmType
     */
    public function getOnlyPsalmType()
    {
    }

    /**
     * Description.
     * @return OnlyPHPStanType
     */
    public function getOnlyPHPStanType()
    {
    }
}
