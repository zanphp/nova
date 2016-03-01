<?php
/**
 * Service struct detector
 * User: moyo
 * Date: 9/21/15
 * Time: 7:07 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Finder
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Reflection
     */
    private $ref = null;

    /**
     * @var Objects
     */
    private $objects = null;

    /**
     * Detector constructor.
     */
    public function __construct()
    {
        $this->ref = Reflection::instance();
        $this->objects = Objects::instance();
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceImplementClass($serviceName)
    {
        return $this->ref->getImplementClass($serviceName);
    }

    /**
     * @param $serviceName
     * @return mixed
     */
    public function getServiceImplementObject($serviceName)
    {
        return $this->objects->load($this->getServiceImplementClass($serviceName));
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getInputStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getSpecificationClass($serviceName);

        /**
         * @var \Kdt\Iron\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getInputStructSpec($method);

        return $args;
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getOutputStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getSpecificationClass($serviceName);

        /**
         * @var \Kdt\Iron\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getOutputStructSpec($method);

        return $args;
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getExceptionStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getSpecificationClass($serviceName);

        /**
         * @var \Kdt\Iron\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getExceptionStructSpec($method);

        return $args;
    }
}