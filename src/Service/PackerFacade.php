<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/23
 * Time: 13:23
 */

namespace Kdt\Iron\Nova\Service;



use Kdt\Iron\Nova\Exception\RpcException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Type\TMessageType;

class PackerFacade {
    use InstanceManager;

    public function decodeServiceArgs($serviceName, $methodName, $binArgs)
    {
        $spec = $this->getSpecClass($serviceName);
        $inputStruct = $spec->getInputStructSpec($methodName);

        $args = Packer::getInstance()->decode($binArgs,$inputStruct);
        $args = Convert::argsToArray($args, $inputStruct);

        return $args;
    }

    public function encodeServiceOutput($serviceName, $methodName, $output)
    {
        $spec = $this->getSpecClass($serviceName);
        $outputStruct = $spec->getOutputStructSpec($methodName);
        $exceptionStruct = $spec->getExceptionStructSpec($methodName);

        $packer = Packer::getInstance();
        $package = $packer->struct($outputStruct, $exceptionStruct, $output);

        return $packer->encode(TMessageType::REPLY, $methodName, $package);
    }

    public function encodeServiceException($serviceName, $methodName, $exception)
    {
        //$spec = $this->getSpecClass($serviceName);
        //$outputStruct = $spec->getOutputStructSpec($methodName);
        //$exceptionStruct = $spec->getExceptionStructSpec($methodName);

        return Packer::getInstance()->encode(TMessageType::EXCEPTION, $methodName, $exception);
    }


    private function getSpecClass($serviceName)
    {
        $spec = ClassMap::getInstance()->getSpec($serviceName);
        if(!$spec) {
            throw new RpcException('No such service spec:' . $serviceName);
        }

        return $spec;
    }

}