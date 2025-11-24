<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
        return 0;
    }

    public function stderr($string)
    {
        $this->stdErrBuffer .= $string;
        return 0;
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
