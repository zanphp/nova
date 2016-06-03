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
use Zan\Framework\Utilities\Encrpt\Uuid;

class Client implements Async
{
    private $_conn;
    private $_sock;
    private $_callback;
    private $_packer;
    private $_serviceName;

    private $_reqServiceName;
    private $_reqMethodName;
    private $_reqSeqNo;
    private $_attachmentContent = '{}';
    
    private $_outputStruct;
    private $_exceptionStruct;
    private $_task;

    public function __construct(Connection $conn, $serviceName)
    {
        $this->_conn = $conn;
        $this->_sock = $conn->getSocket();
        $this->_conn->setClientCb([$this, 'recv']);
        $this->_packer = Packer::newInstance();
        $this->_serviceName = $serviceName;
    }

    public function execute(callable $callback, $task)
    {
        $this->_task = $task;
        $this->_callback = $callback;
    }

    /**
     * @param $data
     * @throws NetworkException
     * @throws ProtocolException
     * @return mixed
     */
    public function recv($data) 
    {
        //release connection
        $this->_conn->release();
        $trace = $this->_task->getContext()['trace'];

        if (false === $data or '' == $data) {
            $trace->commit(socket_strerror($this->_sock->errCode));
            throw new NetworkException(
                socket_strerror($this->_sock->errCode),
                $this->_sock->errCode
            );
        }
        $trace->commit(Constant::SUCCESS);

        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN)) {
            if ($serviceName == $this->_reqServiceName 
                    && $methodName == $this->_reqMethodName 
                    && $seqNo == $this->_reqSeqNo) {

                try {
                    $response = $this->_packer->decode(
                        $thriftBIN,
                        $this->_packer->struct($this->_outputStruct, $this->_exceptionStruct)
                    );
                } catch (\Exception $e) {
                    call_user_func($this->_callback, null, $e);
                    return;
                }

                if(isset($response['novaNullResult'])){
                    call_user_func($this->_callback, null);
                    return;
                }

                if(isset($response['novaEmptyList'])){
                    call_user_func($this->_callback, []);
                    return;
                }

                $ret = isset($response[$this->_packer->successKey])
                    ? $response[$this->_packer->successKey]
                    : null;

                call_user_func($this->_callback, $ret);
            } else {
                throw new NetworkException('nova.client.recv.failed ~[retry:out]');
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
        $this->_reqServiceName = $this->_serviceName;
        $this->_reqMethodName = $method;
        $this->_reqSeqNo = nova_get_sequence();
        $thriftBin = $this->_packer->encode(TMessageType::CALL, $method, $inputArguments);
        $sockInfo = $this->_sock->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;
        $this->_attachmentContent = '{}';
        $this->_outputStruct = $outputStruct;
        $this->_exceptionStruct = $exceptionStruct;

        if (nova_encode($this->_reqServiceName, $this->_reqMethodName, $localIp, $localPort, $this->_reqSeqNo, $this->_attachmentContent, $thriftBin, $sendBuffer)) {
            $trace = (yield getContext('trace'));
            $trace->transactionBegin(Constant::NOVA, $this->_reqServiceName . '.' . $this->_reqMethodName);
            $trace->logEvent(Constant::REMOTE_CALL, Constant::SUCCESS, "", Uuid::get());
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                throw new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
            }
            yield $this;
        } else {
            throw new ProtocolException('nova.encoding.failed');
        }
    }
}