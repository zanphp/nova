<?php
/**
 * CLI Daemon
 * User: moyo
 * Date: 11/11/15
 * Time: 11:50 AM
 */

namespace Kdt\Iron\Nova\Console;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Server;

class Daemon
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * create and run
     */
    public function run()
    {
        $server = Server::instance();
        $server->run();
    }
}