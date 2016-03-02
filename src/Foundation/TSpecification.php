<?php
/**
 * Abs TService
 * User: moyo
 * Date: 9/15/15
 * Time: 3:23 PM
 */

namespace Kdt\Iron\Nova\Foundation;

use Kdt\Iron\Nova\Foundation\Traits\ServiceSpecManager;

abstract class TSpecification
{
    /**
     * Spec mgr
     */
    use ServiceSpecManager;

    /**
     * @var string
     */
    protected $serviceName = 'com.youzan.gateway';

    /**
     * @return string
     */
    final public function getServiceName()
    {
        return $this->serviceName;
    }
}