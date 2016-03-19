<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 16:05
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Foundation\TSpecification;

class Registry {
    use InstanceManager;

    private $map = [];

    public function register(TSpecification $class)
    {
        $serviceName = $class->getServiceName();
        if(isset($this->map[$serviceName])){
            throw new FrameworkException('duplicated implement of :' . $serviceName);
        }
        $methods = $class->getServiceMethods();
        $this->map[$serviceName] = $methods;
    }


    public function getAll()
    {
        if(empty($this->map)) {
            return [];
        }

        $ret = [];
        foreach($this->map as $serviceName => $methods) {
            foreach($methods as $method){
                $ret[] = [
                    $serviceName, $method
                ];
            }
        }

        return $ret;
    }



}