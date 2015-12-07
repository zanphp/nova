<?php
/**
 * Network transport
 * User: moyo
 * Date: 9/11/15
 * Time: 1:44 PM
 */

namespace Kdt\Iron\Nova\Network;

use Config;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Pipe\Local;
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
        if (Config::get('run_mode') == 'test')
        {
            if (isset($_SERVER['HTTP_VIA_RPC']) && strtolower($_SERVER['HTTP_VIA_RPC']) == 'nova')
            {
                // use swoole (by add http header [Via-RPC => nova])
            }
            else
            {
                $this->pipe = new Local();
            }
        }
        if (is_null($this->pipe))
        {
            $this->pipe = new Swoole();
        }
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function request($serviceName, $methodName, $thriftBIN)
    {
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