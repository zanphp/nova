<?php
/**
 * Transport for server
 * User: moyo
 * Date: 9/11/15
 * Time: 1:43 PM
 */

namespace Kdt\Iron\Nova\Transport;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Network;

class Server
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Network
     */
    private $network = null;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->network = Network::instance();
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function handle($serviceName, $methodName, $thriftBIN)
    {
        return $this->network->process($serviceName, $methodName, $thriftBIN);
    }
}