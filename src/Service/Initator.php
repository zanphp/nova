<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:56
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Initator {
    use InstanceManager;


    public function __construct()
    {
    }

    public function init($config)
    {
        ConfigValidator::getInstance()->validate($config);

        NovaConfig::getInstance()
            ->setPath($config['path'])
            ->setNamespace($config['namespace']);

        Scanner::getInstance()->scan();
    }
}