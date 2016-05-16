<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:52
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Exception\FrameworkException;

class ConfigValidator {
    use InstanceManager;

    private $config;

    public function __construct()
    {
    }

    public function validate($config)
    {
        $this->config = $config;

        $this->validateNamespace();
        $this->validatePath();
    }

    private function validateNamespace()
    {
        if(!isset($this->config['namespace'])) {
            throw new FrameworkException('nova namespace not defined');
        }
    }

    private function validatePath()
    {
        if(!isset($this->config['path'])) {
            throw new FrameworkException('nova path not defined');
        }

        if(!is_dir($this->config['path'])) {
            throw new FrameworkException('nova path is not valid');
        }
    }
}