<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\db;

/**
 * ActiveRelationTrait implements the common methods and properties for active record relational queries.
 *
 * @method ActiveRecordInterface|array|null one($db = null) See [[ActiveQueryInterface::one()]] for more info.
 * @method list<ActiveRecordInterface> all($db = null) See [[ActiveQueryInterface::all()]] for more info.
 *
 * @property class-string<ActiveRecordInterface> $modelClass
 * @property (int|string)[] $someProperty
 */
trait ActiveRelationTrait
{
    public const SOME_TRAIT_CONST = 'someTraitConst';
}
