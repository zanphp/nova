<?php
/**
 * Service:Objects
 * User: moyo
 * Date: 3/1/16
 * Time: 8:11 PM
 */

namespace Kdt\Iron\Nova\Tests\Service;

use Kdt\Iron\Nova\Service\Objects;

class ObjectsTest extends \PHPUnit_Framework_TestCase
{
    public function test_load()
    {
        $this->assertEquals(true, Objects::instance()->load('\Kdt\Iron\Nova\Tests\Service\ObjectsTest_mock') instanceof ObjectsTest_mock);
    }
}

class ObjectsTest_mock {}