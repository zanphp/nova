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
     * @var array
     */
    private $interfaceCompatible = [];

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
     * @return bool
     */
    public function isInterfaceCompatible($serviceName)
    {
        if (isset($this->interfaceCompatible[$serviceName]))
        {
            $is = $this->interfaceCompatible[$serviceName];
        }
        else
        {
            $interface = $this->ref->getInterfaceClass($serviceName);
            $controller = $this->ref->getServiceController($serviceName);
            $implements = class_implements($controller);
            $this->interfaceCompatible[$serviceName] = $is = isset($implements[substr($interface, 1)]) ? true : false;
        }
        return $is;
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceController($serviceName)
    {
        return $this->ref->getServiceController($serviceName);
    }

    /**
     * @param $serviceName
     * @return mixed
     */
    public function getServiceControllerInstance($serviceName)
    {
        return $this->objects->load($this->getServiceController($serviceName));
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getInputStruct($serviceName, $method)
    {
        $clientCN = $this->ref->getClientClass($serviceName);

        /**
         * @var \Kdt\Iron\Nova\Foundation\TClient
         */
        $clientOJ = $this->objects->load($clientCN);

        $args = $clientOJ->getInputStructSpec($method);

        return $args;
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getOutputStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getServiceClass($serviceName);

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
        $serviceCN = $this->ref->getServiceClass($serviceName);

        /**
         * @var \Kdt\Iron\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getExceptionStructSpec($method);

        return $args;
    }
}