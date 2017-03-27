<?php
/**
 * Abs TException
 * User: moyo
 * Date: 11/4/15
 * Time: 4:45 PM
 */

namespace Kdt\Iron\Nova\Foundation\Protocol;

use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Exception as SysException;

abstract class TException extends SysException
{
    /**
     * Spec mgr
     */
    use StructSpecManager;

    /**
     * TException constructor.
     */
    public function __construct($message = "", $code = 0, SysException $previous = null)
    {
        $this->staticSpecInjecting();
        //Apply for default message defined in .thrift file
        if (!$message and isset($this->message) and $this->message) {
            $message = $this->message;
        }
        parent::__construct($message, $code, $previous);
    }
}
