<?php
/**
 * Spec mgr (for service)
 * User: moyo
 * Date: 9/17/15
 * Time: 8:03 PM
 */

namespace Kdt\Iron\Nova\Foundation\Traits;

trait ServiceSpecManager
{
    /**
     * @var array
     */
    protected $inputStructSpec = [];

    /**
     * @var array
     */
    protected $outputStructSpec = [];

    /**
     * @var array
     */
    protected $exceptionStructSpec = [];

    /**
     * @param $method
     * @return array
     */
    public function getInputStructSpec($method)
    {
        return isset($this->inputStructSpec[$method]) ? $this->inputStructSpec[$method] : null;
    }

    /**
     * @param $method
     * @return array
     */
    public function getOutputStructSpec($method)
    {
        return isset($this->outputStructSpec[$method]) ? $this->outputStructSpec[$method] : null;
    }

    /**
     * @param $method
     * @return array
     */
    public function getExceptionStructSpec($method)
    {
        return isset($this->exceptionStructSpec[$method]) ? $this->exceptionStructSpec[$method] : null;
    }
}