<?php
/**
 * CLI Daemon
 * User: moyo
 * Date: 11/11/15
 * Time: 11:50 AM
 */

namespace Kdt\Lib\Nova\Console;

use Kdt\Lib\Nova\Transport\Server;
use Config;
use swoole_server;

class Daemon
{

    protected $swObj = null;

    protected $swConfig = [];

    protected $masterPidPath = '';

    protected $verbose = false;

    public function  __construct($swConfig = [], $verbose = false)
    {
        $this->runEnvCheck();

        $this->swConfig = $swConfig ?: Config::get('swoole');

        $this->verbose = $verbose;
    }

    public function start()
    {
        $this->swObj = new swoole_server($this->swConfig['host'], $this->swConfig['port']);

        $this->swObj->set($this->swConfig['setting']);

        $this->swObj->on('WorkerStart', array($this, 'doWorkerStart'));

        $this->swObj->on('service', array($this, 'doServiceRequest'));

        $set = Config::get('nova');

        $this->swObj->nova_config($set);

        $this->swObj->start();

    }

    public function doWorkerStart($server, $workerId)
    {
        //php > 5.5 opcache共享扩展安装
        extension_loaded("opcache") && opcache_reset();
    }

    public function doServiceRequest(swoole_server $serv, $fd, $from_id, $service_name, $method_name, $ip, $port, $seq_no, $data)
    {
        //注册致命错误  http://wiki.swoole.com/wiki/page/305.html
        //register_shutdown_function(array($this, 'handleFatal'), [$serv, $fd]);

        //靠，swoole居然不支持  set_exception_handler  @see http://wiki.swoole.com/wiki/page/41.html
        try {

            $buffer = Server::instance()->handle($service_name, $method_name, $data);

            if ($this->verbose)
            {
                echo sprintf('[%s]<||>[%s:%s]<||>%s<||>%s::%s<||>%d', date('Y-m-d H:i:s'), long2ip($ip), $port, $seq_no, $service_name, $method_name, strlen($buffer)), "\n";
            }

            $serv->resp_service($fd, $service_name, $method_name, $ip, $port, $seq_no, $buffer);

        } catch (\Exception $e) {

            if(Config::get('debug')){
                \Exception_Handler::handler($e);
            }else{
                \Exception_Handler::exception_handler_product($e);
            }
        }

    }

    private function runEnvCheck()
    {
        if (!extension_loaded('swoole')) die('please install swoole extension');
    }
}