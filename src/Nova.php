<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:47
 */
namespace Kdt\Iron\Nova;

use Kdt\Iron\Nova\Protocol\Packer;
use Kdt\Iron\Nova\Service\Initator;
use Kdt\Iron\Nova\Service\Registry;
use Kdt\Iron\Nova\Service\NovaConfig;
use Kdt\Iron\Nova\Service\PackerFacade;

class Nova {

    const CLIENT = Packer::CLIENT;
    const SERVER = Packer::SERVER;

    public static function init($config)
    {
        Initator::newInstance()->init($config);
    }

    public static function getAvailableService()
    {
         return Registry::getInstance()->getAll();
    }

    public static function removeNovaNamespace($serviceName)
    {
        return NovaConfig::getInstance()->removeNovaNamespace($serviceName);
    }

    public static function decodeServiceArgs($serviceName, $methodName, $binArgs, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->decodeServiceArgs($serviceName, $methodName, $binArgs, $side);
    }

    public static function encodeServiceOutput($serviceName, $methodName, $output, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->encodeServiceOutput($serviceName, $methodName, $output, $side);
    }

    public static function encodeServiceException($serviceName, $methodName, $exception, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->encodeServiceException($serviceName, $methodName, $exception, $side);
    }

}