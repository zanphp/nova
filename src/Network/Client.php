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
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Sdk\Trace\TraceBuilder;

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
        $key = spl_object_hash($conn) . '_' . $serviceName;
        if (!isset(static::$_instance[$key]) || null === static::$_instance[$key]) {
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
        $this->_currentContext->setTask($task);
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
            $exception = new NetworkException(
                socket_strerror($this->_sock->errCode),
                $this->_sock->errCode
            );

            goto handle_exception;
        }

        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN)) {
            $context = isset(self::$_reqMap[$seqNo]) ? self::$_reqMap[$seqNo] : null;
            if (!$context) {
                throw new NetworkException('nova.client.recv.failed ~[context null]');
            }
            unset(self::$_reqMap[$seqNo]);
            $trace = $context->getTask()->getContext()->get('trace');
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
                    $trace->commit($e->getTraceAsString());
                    call_user_func($cb, null, $e);
                    return;
                }
                if(isset($response['novaNullResult'])){
                    $trace->commit(Constant::SUCCESS);
                    call_user_func($cb, null);
                    return;
                }

                if(isset($response['novaEmptyList'])){
                    $trace->commit(Constant::SUCCESS);
                    call_user_func($cb, []);
                    return;
                }

                $ret = isset($response[$packer->successKey])
                    ? $response[$packer->successKey]
                    : null;

                $trace->commit(Constant::SUCCESS);
                call_user_func($cb, $ret);
                return;
            } 
        } else {
            $exception = new ProtocolException('nova.decoding.failed ~[client:'.strlen($data).']');
            goto handle_exception;
        }

handle_exception:
        foreach (self::$_reqMap as $req) {
            $trace = $req->getTask()->getContext()->get('trace');
            $trace->commit(socket_strerror($this->_sock->errCode));
            $req->getTask()->sendException($exception);
        }

        $this->_conn->close();
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

        $trace = (yield getContext('trace'));

        $trace->transactionBegin(Constant::NOVA, $this->_serviceName . '.' . $method);
        $msgId = TraceBuilder::generateId();
        $trace->logEvent(Constant::REMOTE_CALL, Constant::SUCCESS, "", $msgId);
        $trace->setRemoteCallMsgId($msgId);
        $attachment = [];

        if ($trace->getRootId()) {
            $attachment[Trace::TRACE_KEY]['rootId'] = $trace->getRootId();
        }
        if ($trace->getParentId()) {
            $attachment[Trace::TRACE_KEY]['parentId'] = $trace->getParentId();
        }
        $attachment[Trace::TRACE_KEY]['eventId'] = $msgId;
        if (!empty($this->_attachmentContent)) {
            $this->_attachmentContent = json_encode($attachment);
        }
        
        if (nova_encode($this->_serviceName, $method, $localIp, $localPort, $_reqSeqNo, $_attachmentContent, $thriftBin, $sendBuffer)) {
            $this->_conn->setLastUsedTime();
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                $exception = new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
                goto handle_exception;
            }
            yield $this;
            return;
        } else {
            $exception = new ProtocolException('nova.encoding.failed');
            goto handle_exception;
        }

handle_exception:
        $trace->commit($exception);
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
            $this->_conn->setLastUsedTime();
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