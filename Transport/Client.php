<?php
/**
 * Transport for client
 * User: moyo
 * Date: 9/11/15
 * Time: 1:39 PM
 */

namespace Kdt\Lib\Nova\Transport;

use Kdt\Lib\Nova\Network\Network;
use Kdt\Lib\Nova\Protocol\Packer;
use Kdt\Lib\Nova\Service\Convert;
use Kdt\Lib\Nova\Service\Finder;
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
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Convert
     */
    private $convert = null;

    /**
     * Client constructor.
     * @param $serviceName
     */
    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
        $this->packer = Packer::newInstance();
        $this->network = Network::instance();
        $this->finder = Finder::instance();
        $this->convert = Convert::instance();
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
        $response = $this->packer->decode(
            $this->network->request(
                $this->serviceName,
                $method,
                $this->packer->encode(TMessageType::CALL, $method, $inputArguments)
            ),
            $this->packer->struct($outputStruct, $exceptionStruct)
        );
        $data = isset($response[$this->packer->successKey]) ? $response[$this->packer->successKey] : null;
        $useInterface = $this->finder->isInterfaceCompatible($this->serviceName);
        if ($useInterface)
        {
            $success = $data;
        }
        else
        {
            $success = $this->convert->outputStructToArray($data);
        }
        return $success;
    }
}