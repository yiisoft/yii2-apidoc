<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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
     * @var string
     */
    public $object;
    /**
     * @var TypeDoc|null
     */
    public $context;


    /**
     * Constructor.
     *
     * @param string $object
     * @param TypeDoc|null $context
     */
    public function __construct($object, $context)
    {
        $this->object = $object;
        $this->context = $context;

        $message = 'broken link to ' . $object . (($context !== null) ? ' in ' . $context->name : '');
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
