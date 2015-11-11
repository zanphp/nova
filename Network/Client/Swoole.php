<?php
/**
 * Client for swoole
 * User: moyo
 * Date: 9/28/15
 * Time: 3:11 PM
 */

namespace Kdt\Iron\Nova\Thrift\Network\Client;

use Kdt\Iron\Nova\Thrift\Exception\NetworkException;
use swoole_client as SwooleClient;

class Swoole
{
    /**
     * @var array
     */
    private $config = [
        'open_nova_protocol' => 1
    ];

    /**
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * @var int
     */
    private $port = 10002;

    /**
     * @var int
     */
    private $timeout = 1;

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
        $this->client = new SwooleClient(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $this->client->set($this->config);
        $connected = @$this->client->connect($this->host, $this->port, $this->timeout);
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
     */
    public function send($serviceName, $methodName, $thriftBIN)
    {
        $this->setBusying();
        $sent = @$this->client->call_service($serviceName, $methodName, $thriftBIN);
        if (false === $sent)
        {
            throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
    }

    /**
     * @return string
     * @throws NetworkException
     */
    public function recv()
    {
        $response = @$this->client->recv_service();
        if (false === $response)
        {
            throw new NetworkException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
        $this->setIdling();
        return $response;
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