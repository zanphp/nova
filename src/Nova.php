<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:47
 */
namespace Kdt\Iron\Nova;

use Kdt\Iron\Nova\Service\Initator;
use Kdt\Iron\Nova\Service\Registry;
use Kdt\Iron\Nova\Service\NovaConfig;
use Kdt\Iron\Nova\Service\PackerFacade;

class Nova {
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

    public static function decodeServiceArgs($serviceName, $methodName, $binArgs)
    {
        return PackerFacade::getInstance()->decodeServiceArgs($serviceName, $methodName, $binArgs);
    }

    public static function encodeServiceOutput($serviceName, $methodName, $output)
    {
        return PackerFacade::getInstance()->encodeServiceOutput($serviceName, $methodName, $output);
    }

    public static function encodeServiceException($serviceName, $methodName, $exception)
    {
        return PackerFacade::getInstance()->encodeServiceException($serviceName, $methodName, $exception);
    }

}