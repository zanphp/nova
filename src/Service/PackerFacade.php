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
use Kdt\Iron\Nova\NullResult\NovaEmptyListResult;
use Kdt\Iron\Nova\NullResult\NovaNullResult;
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
        
        $response = $this->parseNullResult($output);
        $withNullExceptions = null !== $response['output'] ? false : true;
        $exceptionStruct = $spec->getExceptionStructSpec($methodName, $withNullExceptions);

        $packer = Packer::getInstance();
        $package = $packer->struct($outputStruct, $exceptionStruct, $response['output'], $response['exception']);

        return $packer->encode(TMessageType::REPLY, $methodName, $package);
    }
    
    protected function parseNullResult($output)
    {
        $response = [
            'output' => $output,
            'exception' => null
        ];
        
        if(null !== $output && [] !== $output){
            return $response;
        }
        
        $response['output'] = null;
        if(null === $output){
            $response['exception'] = new NovaNullResult();
        }
        
        if([] === $output){
            $response['exception'] = new NovaEmptyListResult();
        }
        
        return $response; 
    }

    public function encodeServiceException($serviceName, $methodName, $exceptions)
    {
        $spec = $this->getSpecClass($serviceName);
        $outputStruct = $spec->getOutputStructSpec($methodName);
        $exceptionStruct = $spec->getExceptionStructSpec($methodName, true);

        $packer = Packer::getInstance();
        $package = $packer->struct($outputStruct, $exceptionStruct, null, $exceptions);

        return $packer->encode(TMessageType::REPLY, $methodName, $package);
//        $spec = $this->getSpecClass($serviceName);
//        $outputStruct = $spec->getOutputStructSpec($methodName);
//        $exceptionStruct = $spec->getExceptionStructSpec($methodName);
//
//        return Packer::getInstance()->encode(TMessageType::EXCEPTION, $methodName, $exception);
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