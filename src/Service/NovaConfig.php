<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 16:05
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class NovaConfig {
    use InstanceManager;

    private $map = [];
    private $namespace = null;
    private $path = null;

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function removeNovaNamespace($serviceName)
    {
        $novaNSLen = strlen($this->namespace);

        return substr($serviceName, $novaNSLen);
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = realpath($path) . '/';

        return $this;
    }

    public function set($key, $object)
    {
        $this->map[$key] = $object;
    }

    public function get($key, $default=null)
    {
        if(!isset($this->map[$key])){
            return $default;
        }
        return $this->map[$key];
    }

}