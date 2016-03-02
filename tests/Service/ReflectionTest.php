<?php
/**
 * Service:Reflection
 * User: moyo
 * Date: 3/1/16
 * Time: 8:00 PM
 */

namespace Kdt\Iron\Nova\Tests\Service;

use Kdt\Iron\Nova\Service\Reflection;

class ReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getInterfaceClass()
    {
        $this->assertEquals('\com\youzan\demo\interfaces\entry', Reflection::instance()->getInterfaceClass('com.youzan.demo.service.entry'));
    }

    public function test_getImplementClass()
    {
        $this->assertEquals('\Kdt\App\Demo\Service\Entry', Reflection::instance()->getImplementClass('com.youzan.demo.service.entry'));
    }

    public function test_getServiceClass()
    {
        $this->assertEquals('\com\youzan\demo\service\entry', Reflection::instance()->getServiceClass('com.youzan.demo.service.entry'));
    }

    public function test_getSpecificationClass()
    {
        $this->assertEquals('\com\youzan\demo\servicespecification\entry', Reflection::instance()->getSpecificationClass('com.youzan.demo.service.entry'));
    }
}