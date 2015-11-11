<?php
/**
 * Abs TStruct
 * User: moyo
 * Date: 9/10/15
 * Time: 4:35 PM
 */

namespace Kdt\Lib\Nova\Foundation\Protocol;

use Kdt\Lib\Nova\Foundation\Traits\StructSpecManager;

abstract class TStruct
{
    /**
     * Spec mgr
     */
    use StructSpecManager;

    /**
     * TStruct constructor.
     */
    public function __construct()
    {
        $this->staticSpecInjecting();
    }
}