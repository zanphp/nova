<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:49
 */

namespace Kdt\Iron\Nova\Tests;

use Kdt\Iron\Nova\Nova;

class NovaTest extends UnitTest{

    public function testNovaInit()
    {
        $config = [
            'path'  => __DIR__ . '/resource/sso/sdk/gen-php',
            'namespace' => 'Com\\Youzan\\Sso\\',
        ];

        Nova::init($config);
        var_dump(Nova::getAvailableService());
    }
}