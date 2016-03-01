<?php
/**
 * Service call dispatcher
 * User: moyo
 * Date: 9/22/15
 * Time: 2:30 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Protocol\TException as BizException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Exception as SysException;
use Thrift\Exception\TApplicationException;

class Dispatcher
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Convert
     */
    private $convert = null;

    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        $this->finder = Finder::instance();
        $this->convert = Convert::instance();
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $arguments
     * @return mixed
     */
    public function call($serviceName, $methodName, $arguments)
    {
        try
        {
            $hostingCtrl = $this->finder->getServiceImplementObject($serviceName);
            if (method_exists($hostingCtrl, $methodName))
            {
                $arguments = $this->convert->inputArgsToFuncArray($arguments, $this->finder->getInputStruct($serviceName, $methodName));
                $data = call_user_func_array([$hostingCtrl, $methodName], $arguments);
                $state = $sign = 'success';
            }
            else
            {
                throw new TApplicationException('dispatcher.service.method.missing', TApplicationException::WRONG_METHOD_NAME);
            }
        }
        catch (SysException $e)
        {
            $data = $e;
            if ($e instanceof BizException)
            {
                $state = 'success';
                $sign = 'biz-exception';
            }
            else
            {
                $state = 'failed';
                $sign = 'sys-exception';
            }
        }
        return ['state' => $state, 'sign' => $sign, 'data' => $data];
    }
}