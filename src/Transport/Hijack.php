<?php
/**
 * Hijack component
 * User: moyo
 * Date: 1/22/16
 * Time: 1:57 PM
 */

namespace Kdt\Iron\Nova\Transport;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Transport\Hijack\Component\Ping;
use Kdt\Iron\Nova\Transport\Hijack\Framework;

class Hijack
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * all hijack components
     * @return Framework[]
     */
    private function components()
    {
        return [
            Ping::instance()
        ];
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return mixed
     */
    public function processing($serviceName, $methodName, $thriftBIN)
    {
        $components = $this->components();
        foreach ($components as $component)
        {
            if ($component->matchRequest($serviceName, $methodName, $thriftBIN))
            {
                return $component->makeResponse($serviceName, $methodName, $thriftBIN);
            }
        }
        return null;
    }
}