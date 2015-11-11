<?php
/**
 * CLI Daemon
 * User: moyo
 * Date: 11/11/15
 * Time: 11:50 AM
 */

namespace Kdt\Lib\Nova\Console;

use Kdt\Lib\Nova\Transport\Server;

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

        $this->swConfig = $swConfig;

        $this->masterPidPath = SWOOLE_RUNTIME_PATH. '/swoole_master.pid';

        $this->verbose = $verbose;
    }

    public function start()
    {
        $this->swObj = new swoole_server($this->swConfig['host'], $this->swConfig['port']);

        $this->swObj->set($this->swConfig['setting']);

        //注册回调函数
        $this->swObj->on('start', array($this, 'doMaterStart'));

        $this->swObj->on('WorkerStart', array($this, 'doWorkerStart'));

        $this->swObj->on('WorkerStop', array($this, 'doWorkerStop'));

        $this->swObj->on('service', array($this, 'doServiceRequest'));

        $set = [
            'module' => 'nova-demo',
            'enable_register' => 1,
            'haunt_url' => 'http://127.0.0.1:8082',
            'enable_report' => 1,
            'hawk_url' => 'http://192.168.66.240:8188',
            'report_interval' => 300,
            'services' =>
                [
                    [
                        'service' => 'com.youzan.nova.demo.StudentService',
                        'methods' => ['listStudent', 'deleteStident', 'addStudent', 'getStudent']
                    ]
                ]
        ];

        $this->swObj->nova_config($set);

        $this->swObj->start();

    }

    //@see http://wiki.swoole.com/wiki/page/20.html
    public function stop()
    {
        if (!file_exists($this->masterPidPath)) die('swoole do not running');

        $masterPid = intval(file_get_contents($this->masterPidPath));

        if (function_exists('posix_kill')) {
            posix_kill($masterPid, SIGTERM);
        } else {
            $command = sprintf("kill -s %d `cat %s`", SIGTERM, $this->masterPidPath);
            exec($command);
        }
    }


    //reload worker
    public function reload()
    {
        if (!file_exists($this->masterPidPath)) die('swoole do not running');

        $masterPid = intval(file_get_contents($this->masterPidPath));

        if (function_exists('posix_kill')) {
            posix_kill($masterPid, SIGUSR1);
        } else {
            $command = sprintf("kill -s %d `cat %s`", SIGUSR1, $this->masterPidPath);
            exec($command);
        }
    }


    public function doMaterStart()
    {
        touch($this->masterPidPath);

        file_put_contents($this->masterPidPath, $this->swObj->master_pid);

    }


    public function doWorkerStart($server, $workerId)
    {
        //php > 5.5 opcache共享扩展安装
        extension_loaded("opcache") && opcache_reset();
    }


    public function doWorkerStop()
    {
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

            var_dump($e);

            if(\Config::get('debug')){
                \Exception_Handler::handler($e);
            }else{
                \Exception_Handler::exception_handler_product($e);
            }
        }

    }

    function handleFatal($server, $fd)
    {
        $error = error_get_last();
        $server->send($fd, var_export($error, true));
    }



    private function runEnvCheck()
    {
        if (!extension_loaded('swoole')) die('please install swoole extension');

        if (!defined('SWOOLE_RUNTIME_PATH')) die('please define SWOOLE_RUNTIME_PATH');

        if (!defined('IN_SWOOLE')) die('please run swoole.php');
    }

    private function checkProcessRunning($pid)
    {
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        } else {
            $command = sprintf("kill -s %d %d 2>/dev/null", 0, $pid);
            passthru($command, $status);
            return !$status;
        }
    }
}