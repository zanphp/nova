<?php
/**
 * Service reflection mgr
 * User: moyo
 * Date: 9/22/15
 * Time: 3:45 PM
 */

namespace Kdt\Lib\Nova\Service;

use Kdt\Lib\Nova\Foundation\Traits\InstanceManager;

class Reflection
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $comApiNS = 'com.youzan';

    /**
     * @var string
     */
    private $kdtApiNS = 'kdt.api';

    /**
     * @var array
     */
    private $refCache = ['interface' => [], 'controller' => [], 'client' => [], 'service' => []];

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceController($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'controller', function () use ($serviceName) { return $this->getIronController($serviceName); });
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getInterfaceClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'interface', 'interfaces');
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getClientClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'client', 'client');
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'service', 'service');
    }

    /**
     * @param $serviceName
     * @param $cacheKey
     * @param $scope
     * @return string
     */
    private function getCachedClassName($serviceName, $cacheKey, $scope)
    {
        if (isset($this->refCache[$cacheKey][$serviceName]))
        {
            $class = $this->refCache[$cacheKey][$serviceName];
        }
        else
        {
            $this->refCache[$cacheKey][$serviceName] = $class = is_string($scope) ? $this->getTargetNS($serviceName, $scope) : call_user_func($scope);
        }
        return $class;
    }

    /**
     * @param $serviceName
     * @param $scopeName
     * @return string
     */
    private function getTargetNS($serviceName, $scopeName = null)
    {
        if (substr($serviceName, 0, strlen($this->comApiNS)) == $this->comApiNS)
        {
            $serviceName = $this->kdtApiNS . substr($serviceName, strlen($this->comApiNS));
        }
        $parts = explode('.', $serviceName);
        // pop service part
        $service = ucfirst(array_pop($parts));
        // pop scope part
        $program = array_pop($parts);
        // get namespace part
        return '\\'.implode('\\', $parts).'\\'.$scopeName.'\\'.$program.'\\'.$service;
    }

    /**
     * @param $serviceName
     * @return string
     */
    private function getIronController($serviceName)
    {
        $symbol = $this->getTargetNS($serviceName, '~');
        $namespace = str_replace(['\\'.str_replace('.', '\\', $this->kdtApiNS).'\\', '\\~'], '', $symbol);
        $programs = [];
        $parts = explode('\\', $namespace);
        array_walk($parts, function ($part) use (&$programs) {
            $programs[] = ucfirst($part);
        });
        return implode('_', $programs).'_ApiController';
    }
}