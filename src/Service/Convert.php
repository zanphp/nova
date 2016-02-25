<?php
/**
 * Service convert (IO format)
 * User: moyo
 * Date: 9/25/15
 * Time: 2:49 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Convert
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @param $data
     * @param $struct
     * @return array
     */
    public function inputArgsToFuncArray($data, $struct)
    {
        $pack = [];
        foreach ($struct as $pos => $config)
        {
            $pack[] = isset($data[$config['var']]) ? $data[$config['var']] : null;
        }
        return $pack;
    }
}