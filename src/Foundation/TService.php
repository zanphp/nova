<?php
/**
 * Abs TService
 * User: moyo
 * Date: 9/14/15
 * Time: 8:55 PM
 */

namespace Kdt\Iron\Nova\Foundation;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\NullResult\NovaEmptyListResult;
use Kdt\Iron\Nova\NullResult\NovaEmptyMapResult;
use Kdt\Iron\Nova\NullResult\NovaEmptySetResult;
use Kdt\Iron\Nova\NullResult\NovaNullResult;
use Kdt\Iron\Nova\Network\Client;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\ConnectionManager;

abstract class TService
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
     * @var TSpecification
     */
    private $relatedSpec = null;

    /**
     * @return TSpecification
     */
    abstract protected function specificationProvider();

    /**
     * @param $method
     * @param $args
     * @return array
     */
    final public function getInputStructSpec($method, $args = [])
    {
        $spec = $this->getRelatedSpec()->getInputStructSpec($method);
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
        return $this->getRelatedSpec()->getOutputStructSpec($method);
    }

    /**
     * @param $method
     * @return array
     */
    final public function getExceptionStructSpec($method)
    {
        return $this->getRelatedSpec()->getExceptionStructSpec($method, true);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    final protected function apiCall($method, $arguments)
    {
        $serviceName = $this->getNovaServiceName();
        $connection = (yield ConnectionManager::getInstance()->get($serviceName));
        if ($connection instanceof \Generator) {
            $connection = $connection->current();
        }

        if (!($connection instanceof Connection)) {
            throw new \Exception('get nova connection error');
        }

        $client = new Client($connection, $serviceName);
        yield $client->call($method, $this->getInputStructSpec($method, $arguments), $this->getOutputStructSpec($method), $this->getExceptionStructSpec($method));
    }
    
    final protected function getNovaServiceName()
    {
        $serviceName  = $this->getRelatedSpec()->getServiceName();
        $nameArr = explode('.', $serviceName);
        $className = array_pop($nameArr);
        $nameArr = array_map('lcfirst', $nameArr);
        $nameArr[] = $className;

        return join('.', $nameArr);
    }

    /**
     * @return TSpecification
     */
    final private function getRelatedSpec()
    {
        if (is_null($this->relatedSpec))
        {
            $this->relatedSpec = $this->specificationProvider();
        }
        return $this->relatedSpec;
    }
}