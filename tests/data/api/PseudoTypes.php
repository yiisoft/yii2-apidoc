<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api;

/**
 * Description.
 *
 * @phpstan-type SomeData array{name: string, birthDate: int}
 * @psalm-type SomeData array{name: string, birthDate: int}
 * @psalm-type OnlyPsalmType = (string|array<string, mixed>)
 * @phpstan-type OnlyPHPStanType (string|array<string, mixed>|object)
 */
class PseudoTypes
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
     * @return SomeData['name']
     */
    public function getNameFromSomeData()
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
