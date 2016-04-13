<?php
/**
 * Transport for client
 * User: moyo
 * Date: 9/11/15
 * Time: 1:39 PM
 */

namespace Kdt\Iron\Nova\Transport;

use Kdt\Iron\Nova\Network\Network;
use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Type\TMessageType;

class Client
{
    /**
     * @var string
     */
    private $serviceName = '';

    /**
     * @var Packer
     */
    private $packer = null;

    /**
     * @var Network
     */
    private $network = null;

    /**
     * Client constructor.
     * @param $serviceName
     */
    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
        $this->packer = Packer::newInstance();
        $this->network = Network::instance();
    }

    /**
     * @param $method
     * @param $inputArguments
     * @param $outputStruct
     * @param $exceptionStruct
     * @return mixed
     */
    public function call($method, $inputArguments, $outputStruct, $exceptionStruct)
    {

        $response = $this->network->request(
                $this->serviceName,
                $method,
                $this->packer->encode(TMessageType::CALL, $method, $inputArguments)
        );
        $response = $this->packer->decode(
            $response,
            $this->packer->struct($outputStruct, $exceptionStruct)
        );

        if(isset($response['novaNullResult'])){
            return null;
        }
        
        if(isset($response['novaEmptyList'])){
            return [];
        }
        $ret = isset($response[$this->packer->successKey])
                    ? $response[$this->packer->successKey]
                    : null;

        return $ret;
    }
}