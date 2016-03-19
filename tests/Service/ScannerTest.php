<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 11:09
 */

namespace Kdt\Iron\Nova\Tests\Service;


use Kdt\Iron\Nova\Service\Scanner;

class ScannerTest extends \PHPUnit_Framework_TestCase {
    public function testScanWork()
    {
        $scanner = Scanner::getInstance();

        $dir = __DIR__ . '/../resource/material/sdk/gen-php/';
        $dir = __DIR__ . '/../resource/sso/sdk/gen-php/';


        $data = $scanner->scan($dir);



    }
}