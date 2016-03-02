<?php
/**
 * Service:Scanner
 * User: moyo
 * Date: 3/2/16
 * Time: 5:12 PM
 */

namespace Kdt\Iron\Nova\Tests\Service;

use Kdt\Iron\Nova\Service\Scanner;

class ScannerTest extends \PHPUnit_Framework_TestCase
{
    public function test_scanApis()
    {
        require __DIR__.'/../mocks/service-scanner/src/Service/Entry.php';
        $expectConfig = [['service' => 'com.youzan.test.service.entry', 'methods' => ['methodGet', 'methodPost']]];
        $provideConfig = Scanner::instance()->scanApis(__DIR__.'/../mocks/service-scanner/', 'test');
        $this->assertArraySubset($expectConfig, $provideConfig);
    }
}