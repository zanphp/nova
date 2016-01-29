<?php
/**
 * Client for swoole
 * User: moyo
 * Date: 9/28/15
 * Time: 3:11 PM
 */

namespace Kdt\Iron\Nova\Network\Client;

use Config;

use swoole_client as SwooleClient;

use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Exception\ProtocolException;

class Swoole
{
    /**
     * @var string
     */
    private $connConfKey = 'nova.client';

    /**
     * @var string
     */
    private $swooleConfKey = 'nova.swoole.client';

    /**
     * @var string
     */
    private $attachmentContent = '{}';

    /**
     * @var object
     */
    private $client = null;

    /**
     * @var bool
     */
    private $idle = false;

    /**
     * Swoole constructor.
     */
    public function __construct()
    {
        $connConf = Config::get($this->connConfKey);
        $clientFlags = $connConf['persistent'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_SOCK_TCP;
        $this->client = new SwooleClient($clientFlags);
        $this->client->set(Config::get($this->swooleConfKey));
        $connected = $this->client->connect($connConf['host'], $connConf['port'], $connConf['timeout']);
        if ($connected)
        {
            $this->setIdling();
        }
        else
        {
            throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @throws NetworkException
     * @throws ProtocolException
     */
    public function send($serviceName, $methodName, $thriftBIN)
    {
        $this->setBusying();
        $sockInfo = $this->client->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $seqNo = nova_get_sequence();
        $sendBuffer = null;
        if (nova_encode($serviceName, $methodName, $localIp, $localPort, $seqNo, $this->attachmentContent, $thriftBIN, $sendBuffer))
        {
            $sent = $this->client->send($sendBuffer);
            if (false === $sent)
            {
                throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
            }
        }
        else
        {
            throw new ProtocolException('nova.encoding.failed');
        }
    }

    /**
     * @return string
     * @throws NetworkException
     * @throws ProtocolException
     */
    public function recv()
    {
        $data = $this->client->recv();
        if (false === $data)
        {
            throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
        $this->setIdling();
        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN))
        {
            return $thriftBIN;
        }
        else
        {
            throw new ProtocolException('nova.decoding.failed ~[client:'.strlen($data).']');
        }
    }

    /**
     * @return bool
     */
    public function idle()
    {
        return $this->idle;
    }

    /**
     * set client idling
     */
    private function setIdling()
    {
        $this->idle = true;
    }

    /**
     * set client busying
     */
    private function setBusying()
    {
        $this->idle = false;
    }
}