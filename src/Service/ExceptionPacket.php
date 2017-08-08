<?php

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TException as ThriftException;
use Exception as SysException;

use ZanPHP\Exception\ZanException;

class ExceptionPacket
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $injectTag = 'IRON-E';

    /**
     * @var string
     */
    private $placeTag = '<||>';

    /**
     * @param SysException $e
     * @return SysException
     */
    public function ironInject(SysException $e)
    {
        if ($e instanceof ThriftException)
        {
            return $e;
        }
        else
        {
            return new TApplicationException($e instanceof ZanException ? $this->messageInject($e) : $e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param SysException $e
     * @return string
     */
    private function messageInject(SysException $e)
    {
        return sprintf('<%s[%s]>%s%s', $this->injectTag, get_class($e), $this->placeTag, $e->getMessage());
    }
}