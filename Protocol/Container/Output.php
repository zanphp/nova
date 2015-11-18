<?php
/**
 * Container of output (bin-acc)
 * User: moyo
 * Date: 10/23/15
 * Time: 4:02 PM
 */

namespace Kdt\Iron\Nova\Protocol\Container;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Output
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

    /**
     * @return array
     */
    public function export()
    {
        $export = [];
        foreach (self::$_TSPEC as $spec)
        {
            $export[$spec['var']] = $this->$spec['var'];
        }
        return $export;
    }
}