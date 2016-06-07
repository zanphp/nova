<?php
/**
 * Client manager
 * User: moyo
 * Date: 9/28/15
 * Time: 3:16 PM
 */

namespace Kdt\Iron\Nova\Network;

use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Type\TMessageType;
use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Exception\ProtocolException;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Client implements Async
{
    private $_conn;
    private $_sock;
    private $_serviceName;
    private $_currentContext;
    private static $_reqMap = [];

    private static $_instance = null;

    final public static function getInstance(Connection $conn, $serviceName)
    {
        $key = spl_object_hash($conn);
        if (null === static::$_instance[$key]) {
            static::$_instance[$key] = new self($conn, $serviceName);
        }
        return static::$_instance[$key];
    }

    public function __construct(Connection $conn, $serviceName)
    {
        $this->_conn = $conn;
        $this->_sock = $conn->getSocket();
        $this->_conn->setClientCb(function($data) {
            $this->recv($data);
        });
        $this->_serviceName = $serviceName;
    }

    public function execute(callable $callback, $task)
    {
        $this->_currentContext->setCb($callback);
    }

    /**
     * @param $data
     * @throws NetworkException
     * @throws ProtocolException
     * @return mixed
     */
    public function recv($data) 
    {
        if (false === $data or '' == $data) {
            throw new NetworkException(
                socket_strerror($this->_sock->errCode),
                $this->_sock->errCode
            );
        }

        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN)) {
            $context = isset(self::$_reqMap[$seqNo]) ? self::$_reqMap[$seqNo] : null;
            if (!$context) {
                throw new NetworkException('nova.client.recv.failed ~[context null]');
            }
            unset(self::$_reqMap[$seqNo]);
            $cb = $context->getCb();
            if ($serviceName === 'com.youzan.service.test' && $methodName === 'pong') {
                return $this->pong($cb);
            }
            $packer = $context->getPacker();

            if ($serviceName == $context->getReqServiceName()
                    && $methodName == $context->getReqMethodName()) {

                try {
                    $response = $packer->decode(
                        $thriftBIN,
                        $packer->struct($context->getOutputStruct(), $context->getExceptionStruct())
                    );
                } catch (\Exception $e) {
                    call_user_func($cb, null, $e);
                    return;
                }

                if(isset($response['novaNullResult'])){
                    call_user_func($cb, null);
                    return;
                }

                if(isset($response['novaEmptyList'])){
                    call_user_func($cb, []);
                    return;
                }

                $ret = isset($response[$packer->successKey])
                    ? $response[$packer->successKey]
                    : null;

                call_user_func($cb, $ret);
            } 
        } else {
            throw new ProtocolException('nova.decoding.failed ~[client:'.strlen($data).']');
        }
    }


    /**
     * @param $method
     * @param $inputArguments
     * @param $outputStruct
     * @param $exceptionStruct
     * @return \Generator
     * @throws NetworkException
     * @throws ProtocolException
     */
    public function call($method, $inputArguments, $outputStruct, $exceptionStruct)
    {
        $_reqSeqNo = nova_get_sequence(); 
        $_attachmentContent = '{}';
        $_packer = Packer::newInstance();
        
        $context = new ClientContext();
        $context->setAttachmentContent($_attachmentContent);
        $context->setOutputStruct($outputStruct);
        $context->setExceptionStruct($exceptionStruct);
        $context->setReqServiceName($this->_serviceName);
        $context->setReqMethodName($method);
        $context->setReqSeqNo($_reqSeqNo);
        $context->setPacker($_packer);
        
        self::$_reqMap[$_reqSeqNo] = $context;
        $this->_currentContext = $context;
        
        $thriftBin = $_packer->encode(TMessageType::CALL, $method, $inputArguments);
        $sockInfo = $this->_sock->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;

        if (nova_encode($this->_serviceName, $method, $localIp, $localPort, $_reqSeqNo, $_attachmentContent, $thriftBin, $sendBuffer)) {
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                throw new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
            }
            yield $this;
        } else {
            throw new ProtocolException('nova.encoding.failed');
        }
    }

    public function ping()
    {
        $_reqSeqNo = nova_get_sequence();
        $method = 'ping';
        $context = new ClientContext();
        $context->setReqServiceName($this->_serviceName);
        $context->setReqMethodName($method);
        $context->setReqSeqNo($_reqSeqNo);

        self::$_reqMap[$_reqSeqNo] = $context;
        $this->_currentContext = $context;

        $sockInfo = $this->_sock->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;

        if (nova_encode($this->_serviceName, $method, $localIp, $localPort, $_reqSeqNo, '', '', $sendBuffer)) {
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                throw new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
            }
            yield $this;
        } else {
            throw new ProtocolException('nova.encoding.failed');
        }
    }

    public function pong($cb)
    {
        call_user_func($cb, true);
        return;
    }
}