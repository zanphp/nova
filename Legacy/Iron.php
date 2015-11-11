<?php
/**
 * Legacy iron client
 * User: moyo
 * Date: 9/29/15
 * Time: 8:11 PM
 */

namespace Kdt\Iron\Nova\Thrift\Legacy;

use Kdt\Iron\Nova\Thrift\Exception\NetworkException;
use Kdt\Iron\Nova\Thrift\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Thrift\Service\Convert;
use Kdt\Iron\Nova\Thrift\Service\Finder;
use Kdt\Iron\Nova\Thrift\Service\Objects;

class Iron
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $clientPrefix = '\\kdt\\api';

    /**
     * @var string
     */
    private $clientNamespace = 'client';

    /**
     * @var array
     */
    private $supportCache = [];

    /**
     * @var array
     */
    private $targetClientCache = [];

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Objects
     */
    private $objects = null;

    /**
     * @var Convert
     */
    private $convert = null;

    /**
     * Iron constructor.
     */
    public function __construct()
    {
        $this->finder = Finder::instance();
        $this->objects = Objects::instance();
        $this->convert = Convert::instance();
    }

    /**
     * check if support RPC
     * @param $apiURI
     * @return bool
     */
    public function support($apiURI)
    {
        if (isset($this->supportCache[$apiURI]))
        {
            $support = $this->supportCache[$apiURI];
        }
        else
        {
            list($client) = $this->findTargetClient($apiURI);
            $this->supportCache[$apiURI] = $support = class_exists($client) ? true : false;
        }
        return $support;
    }

    /**
     * @param $apiURI
     * @param $arguments
     * @return mixed
     */
    public function transport($apiURI, $arguments)
    {
        list($client, $method) = $this->findTargetClient($apiURI);

        try
        {
            $result = call_user_func_array([$this->loadClientObject($client), $method], $this->convert->inputArgsToFuncArray($arguments, $this->loadClientObject($client)->getInputStructSpec($method)));
        }
        catch (NetworkException $e)
        {
            return null;
        }

        return $result;
    }

    /**
     * @param $className
     * @return object
     */
    private function loadClientObject($className)
    {
        return $this->objects->load($className);
    }

    /**
     * @param $api
     * @return array
     */
    private function findTargetClient($api)
    {
        if (isset($this->targetClientCache[$api]))
        {
            $target = $this->targetClientCache[$api];
        }
        else
        {
            $programs = explode('.', $api);
            // get app
            $app = array_shift($programs);
            // get method
            $method = array_pop($programs);
            // get service
            $service = array_pop($programs);
            // prepend header
            array_unshift($programs, $this->clientNamespace);
            // append service
            array_push($programs, ucfirst($service));
            // generate target
            $this->targetClientCache[$api] = $target = [implode('\\', array_merge([$this->clientPrefix, $app], $programs)), $method];
        }
        return $target;
    }
}