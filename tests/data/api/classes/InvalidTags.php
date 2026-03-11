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
 * @phpstan-type InvalidPHPStanType
 * @phpstan-import-type InvalidPHPStanImportType
 * @psalm-type InvalidPsalmType
 * @psalm-import-type InvalidPsalmImportType
 *
 * @see NonExistentClass
 */
class InvalidTags
{
    /** @var */
    public $invalidVar;

    /**
     * Description.
     *
     * @param
     * @param string $nonExistentParam
     * @return
     */
    public function invalidParamAndReturn($param)
    {
    }
}
