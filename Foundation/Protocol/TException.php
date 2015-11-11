<?php
/**
 * Abs TException
 * User: moyo
 * Date: 11/4/15
 * Time: 4:45 PM
 */

namespace Kdt\Lib\Nova\Foundation\Protocol;

use Kdt\Lib\Nova\Foundation\Traits\StructSpecManager;
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
    public function __construct()
    {
        $this->staticSpecInjecting();
    }
}