<?php
/**
 * Service scanner
 * User: moyo
 * Date: 12/3/15
 * Time: 6:16 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Utils\Dir;

class Scanner
{
    use InstanceManager;

    public function scan()
    {
        $config = NovaConfig::getInstance()->getConfig();
        foreach ($config as $item) {
            $this->scanSpec($item["appName"], $item["domain"], $item["path"], $item["namespace"]);
        }
    }

    private function scanSpec($appName, $domain, $path, $baseNamespace)
    {
        /* @var $classMap ClassMap */
        $classMap = ClassMap::getInstance();
        /* @var $registry Registry */
        $registry = Registry::getInstance();

        $pattern = '/servicespecification/';
        $files = Dir::glob($path, $pattern);

        foreach($files as $file){
            $className = $this->getClassNameFromPath($file,$path,'.php');
            $className = $baseNamespace . $className;
            $class = new $className();

            $classMap->setSpec($className,$class);
            $registry->register(Registry::PROTO_NOVA, $domain, $appName, $class);
        }
    }

    private function getClassNameFromPath($path, $prefix, $suffix)
    {
        $strPos = strlen($prefix);
        $endPos = -1 * strlen($suffix);
        $strlen = strlen($path);
        if($strlen < $strPos) {
            throw new FrameworkException('get spec className from path failed');
        }
        if($strlen < -1 * ($endPos)) {
            throw new FrameworkException('get spec className from path failed');
        }

        $className = substr($path, $strPos, $endPos);
        $className = str_replace('/', '\\', $className);

        return $className;
    }

}