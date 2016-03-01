<?php
/**
 * Abs TClient
 * User: moyo
 * Date: 9/14/15
 * Time: 8:55 PM
 */

namespace Kdt\Iron\Nova\Foundation;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Transport\Client;

abstract class TClient
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Client
     */
    private $client = null;

    /**
     * @var TService
     */
    private $relateService = null;

    /**
     * @return TService
     */
    abstract protected function serviceProvider();

    /**
     * @param $method
     * @param $args
     * @return array
     */
    final public function getInputStructSpec($method, $args = [])
    {
        $spec = $this->getRelateService()->getInputStructSpec($method);
        foreach ($args as $i => $arg)
        {
            $spec[$i + 1]['value'] = $arg;
        }
        return $spec;
    }

    /**
     * @param $method
     * @return array
     */
    final public function getOutputStructSpec($method)
    {
        return $this->getRelateService()->getOutputStructSpec($method);
    }

    /**
     * @param $method
     * @return array
     */
    final public function getExceptionStructSpec($method)
    {
        return $this->getRelateService()->getExceptionStructSpec($method);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    final protected function apiCall($method, $arguments)
    {
        return $this->getClient()->call($method, $this->getInputStructSpec($method, $arguments), $this->getOutputStructSpec($method), $this->getExceptionStructSpec($method));
    }

    /**
     * @return Client
     */
    final private function getClient()
    {
        if (is_null($this->client))
        {
            $this->client = new Client($this->getRelateService()->getServiceName());
        }
        return $this->client;
    }

    /**
     * @return TService
     */
    final private function getRelateService()
    {
        if (is_null($this->relateService))
        {
            $this->relateService = $this->serviceProvider();
        }
        return $this->relateService;
    }
}