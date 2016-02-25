<?php
/**
 * Hijack framework
 * User: moyo
 * Date: 1/22/16
 * Time: 2:00 PM
 */

namespace Kdt\Iron\Nova\Transport\Hijack;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

abstract class Framework
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return bool
     */
    abstract public function matchRequest($serviceName, $methodName, $thriftBIN);

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return mixed
     */
    abstract public function makeResponse($serviceName, $methodName, $thriftBIN);
}