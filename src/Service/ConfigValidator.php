<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:52
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class ConfigValidator {
    use InstanceManager;

    private $config;

    public function __construct()
    {
    }

    public function validate($config)
    {
        $this->config = $config;
    }

    private function validateNamespace()
    {

    }

    private function validatePath()
    {

    }
}