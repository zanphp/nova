<?php
/**
 * Network transport
 * User: moyo
 * Date: 9/11/15
 * Time: 1:44 PM
 */

namespace Kdt\Iron\Nova\Network;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Pipe\Swoole;

class Network
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Pipe
     */
    private $pipe = null;

    /**
     * Transport constructor.
     */
    public function __construct()
    {
        $this->pipe = new Swoole();
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function request($serviceName, $methodName, $thriftBIN)
    {
        echo "client request .....\n";
        if ($this->pipe->send($serviceName, $methodName, $thriftBIN))
        {
            return $this->pipe->recv();
        }
        else
        {
            return null;
        }
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function process($serviceName, $methodName, $thriftBIN)
    {
        return $this->pipe->process($serviceName, $methodName, $thriftBIN);
    }
}