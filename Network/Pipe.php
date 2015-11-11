<?php
/**
 * Network via (?)
 * User: moyo
 * Date: 9/21/15
 * Time: 3:51 PM
 */

namespace Kdt\Lib\Nova\Network;

use Kdt\Lib\Nova\Exception\FrameworkException;
use Kdt\Lib\Nova\Protocol\Packer;
use Kdt\Lib\Nova\Service\Finder;
use Kdt\Lib\Nova\Service\Dispatcher;
use Thrift\Type\TMessageType;

abstract class Pipe
{
    /**
     * @var Packer
     */
    private $packer = null;

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Dispatcher
     */
    private $dispatcher = null;

    /**
     * Via constructor.
     */
    final public function __construct()
    {
        $this->packer = Packer::newInstance();
        $this->finder = Finder::instance();
        $this->dispatcher = Dispatcher::instance();
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $inputBIN
     * @return string
     * @throws FrameworkException
     */
    final public function process($serviceName, $methodName, $inputBIN)
    {
        // decoding
        $inputArguments = $this->packer->decode($inputBIN, $this->finder->getInputStruct($serviceName, $methodName));
        // dispatching
        $response = $this->dispatcher->call($serviceName, $methodName, $inputArguments);
        if ($response['state'] === 'success')
        {
            // prepare structs
            $successStruct = $this->finder->getOutputStruct($serviceName, $methodName);
            $exceptionStruct = $this->finder->getExceptionStruct($serviceName, $methodName);
            // response data
            if ($response['sign'] === 'success')
            {
                $success = $response['data'];
                $exception = null;
            }
            else if ($response['sign'] === 'biz-exception')
            {
                $success = null;
                $exception = $response['data'];
            }
            else
            {
                throw new FrameworkException('dispatcher.response.struct.illegal');
            }
            // encoding
            $package = $this->packer->struct($successStruct, $exceptionStruct, $success, $exception);
            $outputBIN = $this->packer->encode(TMessageType::REPLY, $methodName, $package);
        }
        else
        {
            // exceptions
            if ($response['state'] === 'failed' && $response['sign'] === 'sys-exception')
            {
                $outputBIN = $this->packer->encode(TMessageType::EXCEPTION, $methodName, $response['data']);
            }
            else
            {
                throw new FrameworkException('dispatcher.response.struct.illegal');
            }
        }
        // over
        return $outputBIN;
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    abstract public function send($serviceName, $methodName, $thriftBIN);

    /**
     * @return string
     */
    abstract public function recv();
}