<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 15:04
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class ClassMap {
    use InstanceManager;

    private $sepcMap = [];
    public function setSpec($key, $object)
    {
        $this->sepcMap[$key] = $object;
    }

    public function getSpec($key, $default=null)
    {
        if(!isset($this->sepcMap[$key])){
            return $default;
        }
        return $this->sepcMap[$key];
    }

    public function getAllSpec()
    {
        return $this->sepcMap;
    }

}