<?php
/**
 * Client for swoole
 * User: moyo
 * Date: 9/28/15
 * Time: 3:11 PM
 */

namespace Kdt\Iron\Nova\Network\Client;

use swoole_client as SwooleClient;

use Zan\Framework\Foundation\Core\Config;
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
    private $swooleConfKey = 'nova.client.config';

    /**
     * @var string
     */
    private $attachmentContent = '{}';

    /**
     * @var string
     */
    private $reqServiceName = '';

    /**
     * @var string
     */
    private $reqMethodName = '';

    /**
     * @var string
     */
    private $reqSeqNo = '';

    /**
     * @var int
     */
    private $recvRetryMax = 3;

    /**
     * @var int
     */
    private $recvRetried = 0;

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
        $this->reqServiceName = $serviceName;
        $this->reqMethodName = $methodName;
        $this->reqSeqNo = nova_get_sequence();
        $this->recvRetried = 0;
        $sockInfo = $this->client->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;
        $this->attachmentContent = '{}';

        // TODO tmp add is _admin
        if (nova_encode($this->reqServiceName, $this->reqMethodName, $localIp, $localPort, $this->reqSeqNo, $this->attachmentContent, $thriftBIN, $sendBuffer))
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
        if (false === $data or '' == $data)
        {
            throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN))
        {
            if ($serviceName == $this->reqServiceName && $methodName == $this->reqMethodName && $seqNo == $this->reqSeqNo)
            {
                $this->setIdling();
                return $thriftBIN;
            }
            else
            {
                if ($this->recvRetried < $this->recvRetryMax)
                {
                    $this->recvRetried ++;
                    return $this->recv();
                }
                else
                {
                    throw new NetworkException('nova.client.recv.failed ~[retry:out]');
                }
            }
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