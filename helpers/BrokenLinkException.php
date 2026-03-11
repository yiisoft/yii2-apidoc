<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use yii\apidoc\models\TypeDoc;
use yii\base\Exception;

/**
 * BrokenLinkException represents a broken API link.
 *
 * @author Brandon Kelly
 * @since 2.1.3
 */
class BrokenLinkException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $object
     * @param TypeDoc|null $context
     */
    public function __construct(public $object, public $context)
    {
        $message = 'broken link to ' . $this->object . (($this->context !== null) ? ' in ' . $this->context->name : '');
        parent::__construct($message);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Broken Link';
    }
}
