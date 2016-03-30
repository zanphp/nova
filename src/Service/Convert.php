<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/24
 * Time: 10:59
 */

namespace Kdt\Iron\Nova\Service;



class Convert {

    public static function argsToArray($data, $struct)
    {
        $pack = [];
        foreach ($struct as $pos => $config)
        {
            $pack[] = isset($data[$config['var']]) ? $data[$config['var']] : null;
        }
        return $pack;
    }
}