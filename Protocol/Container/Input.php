<?php
/**
 * Container of input (bin-acc)
 * User: moyo
 * Date: 10/23/15
 * Time: 4:02 PM
 */

namespace Kdt\Lib\Nova\Protocol\Container;

use Kdt\Lib\Nova\Foundation\Traits\InstanceManager;

class Input
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var array
     */
    public static $_TSPEC = [];

    /**
     * @param $TSPEC
     */
    public function setTSPEC($TSPEC)
    {
        self::$_TSPEC = $TSPEC;
    }
}