<?php
/**
 * Server for swoole
 * User: moyo
 * Date: 12/2/15
 * Time: 4:48 PM
 */

namespace Kdt\Iron\Nova\Network\Server;

use Config;

use swoole_server as SwooleServer;

use Kdt\Iron\Nova\Transport\Server as TransportServer;
use Exception as SysException;

class Swoole
{
    /**
     * @var SwooleServer
     */
    private $instance = null;

    /**
     * @var bool
     */
    private $verboseMode = false;

    /**
     * @var string
     */
    private $swooleConfKey = 'nova.swoole.server';

    /**
     * @var string
     */
    private $attachmentContent = '{}';

    /**
     * @var string
     */
    private $processorExceptionB64 = 'gAEAAwAAABBzZXJ2ZXIucHJvY2Vzc29yAAAAAAsAAQAAABpzZXJ2ZXIucHJvY2Vzc29yLmV4Y2VwdGlvbggAAgAAAAAA';

    /**
     * @param $verboseMode
     * @param $serverConfig
     * @param $platformConfig
     */
    public function startup($verboseMode, $serverConfig, $platformConfig)
    {
        $this->verboseMode = $verboseMode;
        $this->instance = new SwooleServer($serverConfig['host'], $serverConfig['port']);
        $this->instance->set(Config::get($this->swooleConfKey));
        $this->instance->nova_config($platformConfig);
        $this->instance->on('WorkerStart', [$this, 'processWorkerStart']);
        $this->instance->on('service', [$this, 'processServiceRequest']);
        $this->instance->start();
    }

    /**
     * process worker-starting
     */
    public function processWorkerStart()
    {
        if (extension_loaded('opcache'))
        {
            opcache_reset();
        }
    }

    /**
     * process service-requesting
     * @param SwooleServer $server
     * @param $fd
     * @param $from_id
     * @param $serviceName
     * @param $methodName
     * @param $remoteIP
     * @param $remotePort
     * @param $seqNo
     * @param $dataBuffer
     */
    public function processServiceRequest(SwooleServer $server, $fd, $from_id, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $dataBuffer)
    {
        try
        {
            $outputBuffer = TransportServer::instance()->handle($serviceName, $methodName, $dataBuffer);
            if ($this->verboseMode)
            {
                echo sprintf('[%s]<||>[%s:%s]<||>%s<||>%s::%s<||>%d:%d', date('Y-m-d H:i:s'), long2ip($remoteIP), $remotePort, $seqNo, $serviceName, $methodName, strlen($dataBuffer), strlen($outputBuffer)), "\n";
            }
            $server->resp_service($fd, $serviceName, $methodName, $this->attachmentContent, $remoteIP, $remotePort, $seqNo, $outputBuffer);
        }
        catch (SysException $e)
        {
            // sending default exception bin
            $server->resp_service($fd, $serviceName, $methodName, $this->attachmentContent, $remoteIP, $remotePort, $seqNo, base64_decode($this->processorExceptionB64));
        }
    }
}