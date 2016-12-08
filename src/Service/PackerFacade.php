<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/23
 * Time: 13:23
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Exception\NovaException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TMessageType;

class PackerFacade {
    use InstanceManager;

    public function decodeServiceArgs($serviceName, $methodName, $binArgs)
    {
        $spec = $this->getSpecClass($serviceName);
        if (!$spec) {
            throw new NovaException("no such serviceName spec");
        }
        $inputStruct = $spec->getInputStructSpec($methodName);

        $args = Packer::getInstance()->decode($binArgs,$inputStruct);
        $args = Convert::argsToArray($args, $inputStruct);

        return $args;
    }

    public function encodeServiceOutput($serviceName, $methodName, $output)
    {
        $spec = $this->getSpecClass($serviceName);
        if (!$spec) {
            throw new NovaException("no such serviceName");
        }

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
        
//        if(null !== $output && [] !== $output){
//            return $response;
//        }
//
//        $response['output'] = null;
//        if(null === $output){
//            $response['exception'] = new NovaNullResult();
//        }
//
//        if([] === $output){
//            $response['exception'] = new NovaEmptyListResult();
//        }
        
        return $response; 
    }

    public function encodeServiceException($serviceName, $methodName, $exceptions)
    {
        $packer = Packer::getInstance();

        $tApplicationMsg = $tApplicationCode = null;
        $tApplicationMethod = '';

        do {
            if (!$serviceName || !$methodName) {
                $tApplicationCode = TApplicationException::PROTOCOL_ERROR;
                $tApplicationMsg = $exceptions->getMessage();
                break;
            }

            $tApplicationMethod = $methodName;
            $spec = $this->getSpecClass($serviceName);
            if (!$spec) {
                $tApplicationCode = TApplicationException::INTERNAL_ERROR;
                $tApplicationMsg = "No such service spec";
                break;
            }

            $outputStruct = $spec->getOutputStructSpec($methodName);
            if (!$outputStruct) {
                $tApplicationCode = TApplicationException::WRONG_METHOD_NAME;
                $tApplicationMsg = "No such method output";
                break;
            }

            $exceptionStruct = $spec->getExceptionStructSpec($methodName);

            if ($this->isBizException($exceptions, $exceptionStruct)) {
                $package = $packer->struct($outputStruct, $exceptionStruct, null, $exceptions);
            } else {
                $tApplicationCode = TApplicationException::UNKNOWN;
                $tApplicationMsg = $exceptions->getMessage();
                break;
            }
            //biz exception
            return $packer->encode(TMessageType::REPLY, $methodName, $package);
        } while(0);

        //application exception
        $e = new TApplicationException($tApplicationMsg, $tApplicationCode);
        return $packer->encode(TMessageType::EXCEPTION, $tApplicationMethod, $e);
    }


    private function getSpecClass($serviceName)
    {
        $spec = ClassMap::getInstance()->getSpec($serviceName);
        if(!$spec) {
            return null;
        }

        return $spec;
    }

    private function isBizException($e, $exceptionStruct)
    {
        $bizExceptions = [];

        if (empty($exceptionStruct)) {
            return false;
        }
        
        foreach ($exceptionStruct as $bizException) {
            $bizExceptions[] = ltrim($bizException['class'], '\\');
        }
        
        return in_array(ltrim(get_class($e), '\\'), $bizExceptions)
                    ? true : false;
    }

}