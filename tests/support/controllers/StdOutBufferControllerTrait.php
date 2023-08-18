<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\support\controllers;

/**
 * StdOutBufferControllerTrait is a trait, which can be applied to [[yii\console\Controller]],
 * allowing to store all output into internal buffer instead of direct sending it to 'stdout'.
 */
trait StdOutBufferControllerTrait
{
    /**
     * @var string output buffer.
     */
    private $stdOutBuffer = '';
    /**
     * @var string error buffer.
     */
    private $stdErrBuffer = '';


    public function stdout($string)
    {
        $this->stdOutBuffer .= $string;
    }

    public function stderr($string)
    {
        $this->stdErrBuffer .= $string;
    }

    public function flushStdOutBuffer()
    {
        $result = $this->stdOutBuffer;
        $this->stdOutBuffer = '';
        return $result;
    }

    public function flushStdErrBuffer()
    {
        $result = $this->stdErrBuffer;
        $this->stdErrBuffer = '';
        return $result;
    }
}
