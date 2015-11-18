<?php
/**
 * Client manager
 * User: moyo
 * Date: 9/28/15
 * Time: 3:16 PM
 */

namespace Kdt\Iron\Nova\Network;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Client\Swoole;

class Client
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Swoole[]
     */
    private $pool = [];

    /**
     * @return Swoole
     */
    public function idling()
    {
        shuffle($this->pool);
        foreach ($this->pool as $connect)
        {
            if ($connect->idle())
            {
                return $connect;
            }
        }
        return $this->create();
    }

    /**
     * @return Swoole
     */
    private function create()
    {
        $this->pool[] = $client = new Swoole();
        return $client;
    }
}