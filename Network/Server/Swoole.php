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
use Kdt\Iron\Nova\Exception\ProtocolException;
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
        $this->instance->on('receive', [$this, 'processServiceRequest']);
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
     * @param $data
     */
    public function processServiceRequest(SwooleServer $server, $fd, $from_id, $data)
    {
        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $novaData = $attachData = $execResult = $outputBuffer = null;
        try
        {
            if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $novaData))
            {
                $execResult = TransportServer::instance()->handle($serviceName, $methodName, $novaData);
            }
            else
            {
                throw new ProtocolException('nova.decoding.failed ~[server:'.strlen($data).']');
            }
            if ($this->verboseMode)
            {
                echo sprintf('[%s]<||>[%s:%s]<||>%s<||>%s::%s<||>%d:%d', date('Y-m-d H:i:s'), long2ip($remoteIP), $remotePort, $seqNo, $serviceName, $methodName, strlen($novaData), strlen($outputBuffer)), "\n";
            }
        }
        catch (SysException $e)
        {
            // default exception bin
            $execResult = base64_decode($this->processorExceptionB64);
        }
        // encoding && sending
        if (nova_encode($serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $this->attachmentContent, $execResult, $outputBuffer))
        {
            $server->send($fd, $outputBuffer);
        }
        else
        {
            $server->send($fd, 'NOVA.ENCODING.FAILED');
        }
    }
}