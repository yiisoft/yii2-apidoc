<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

/**
 * Description.
 */
class InlineTags
{
    public const SOME_CONST = 'SOME_CONST';

    /** description */
    public $someProperty;

    /**
     * Some description. See {@see InlineTags::inlineLink() parent method}.
     *
     * See {@see InlineTags::SOME_CONST}.
     *
     * See {@see InlineTags::$someProperty}.
     *
     * See {@see InlineTags}.
     *
     * See {@see \yiiunit\apidoc\data\api\classes\GenericTypes}.
     *
     * See {@see https://www.php.net/manual/intro.filter.php documentation}.
     *
     * See {@see InlineTags::inlineLink() }.
     *
     * See {@see \ArrayAccess}.
     *
     * See {@see in_array()}.
     *
     * See {@see \in_array()}.
     *
     * See {@see array_merge() documentation}.
     *
     * See {@see array_merge_recursive()}.
     */
    public function inlineSee(): void
    {
    }

    /**
     * Some description. See {@link https://docs.phpdoc.org/guide/references/phpdoc/tags/link.html documentation}.
     *
     * See invalid link {@link InlineTags::SOME_CONST}.
     *
     * See invalid link with {@link array_merge() description}.
     */
    public function inlineLink(): void
    {
    }
}
