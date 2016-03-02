<?php
/**
 * Server manager
 * User: moyo
 * Date: 12/3/15
 * Time: 2:11 PM
 */

namespace Kdt\Iron\Nova\Network;

use Config;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Server\Swoole;
use Kdt\Iron\Nova\Service\Scanner;

class Server
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $serverConfKey = 'nova.server';

    /**
     * @var string
     */
    private $platformConfKey = 'nova.platform';

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var Swoole
     */
    private $server = null;

    /**
     * @var Scanner
     */
    private $scanner = null;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->server = new Swoole();
        $this->scanner = Scanner::instance();
    }

    /**
     * @param $bool
     */
    public function setVerbose($bool)
    {
        $this->verbose = $bool;
    }

    /**
     * run server
     */
    public function run()
    {
        $this->server->startup(
            $this->verbose,
            Config::get($this->serverConfKey),
            array_merge(
                Config::get($this->platformConfKey), ['services' => $this->scanner->scanApis(ROOT_PATH, APP_NAME)]
            )
        );
    }
}