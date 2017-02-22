<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 16:05
 */

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Zan\Framework\Foundation\Core\Path;

class NovaConfig
{
    use InstanceManager;

    private static $genericInvokePath = "vendor/nova-service/generic/sdk/gen-php";

    private static $genericInvokeBaseNamespace = "Com\\Youzan\\Nova\\";

    private static $required = ["protocol", "domain", "appName", "path", "namespace"];

    private $config = [];

    public function setConfig(array $config)
    {
        self::validatorConfig($config);

        $etcdKeys = []; // 按注册分组

        foreach ($config as &$item) {
            $app = $item["appName"];
            $domain = $item["domain"];
            $proto = $item["protocol"];

            $etcdKey = Registry::buildEtcdKey($proto, $domain, $app);
            $etcdKeys[$etcdKey] = [$proto, $domain, $app];

            $item["path"] = realpath($item["path"]) . '/';
        }
        unset($item);

        // nova 协议 添加 泛化学调用
        foreach ($etcdKeys as list($proto, $domain, $app)) {
            if ($proto === Registry::PROTO_NOVA) {
                $config[] = [
                    "appName" => $app,
                    "domain" => $domain,
                    "path"  => Path::getRootPath() . self::$genericInvokePath . "/",
                    "namespace" => self::$genericInvokeBaseNamespace
                ];
            }
        }

        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    private static function validatorConfig(array $config)
    {
        foreach ($config as $item) {
            foreach (self::$required as $filed) {
                if (!isset($item[$filed])) {
                    throw new FrameworkException("nova $filed not defined");
                }
            }
        }
    }

    // TODO
    public function removeNovaNamespace($proto, $domain, $appName, $serviceName)
    {
        foreach ($this->config as $item) {
            // TODO
            $item["namespace"];

        }

        $novaNSLen = strlen($this->namespace);

        return substr($serviceName, $novaNSLen);
    }
}