<?php
/**
 * Service reflection mgr
 * User: moyo
 * Date: 9/22/15
 * Time: 3:45 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Reflection
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $comApiNS = 'com.youzan';

    /**
     * @var string
     */
    private $kdtAppNS = 'kdt.app';

    /**
     * @var string
     */
    private $srvKeyword = 'service';

    /**
     * @var array
     */
    private $refCache = [
        'interfaces' => [],
        'implements' => [],
        'services' => [],
        'specifications' => []
    ];

    /**
     * @param $serviceName
     * @return string
     */
    public function getInterfaceClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'interfaces', 'interfaces');
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getImplementClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'implements', $this->srvKeyword, $this->kdtAppNS, true);
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'services', $this->srvKeyword);
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getSpecificationClass($serviceName)
    {
        return $this->getCachedClassName($serviceName, 'specifications', 'servicespecification');
    }

    /**
     * @param $serviceName
     * @param $cacheKey
     * @param $scope
     * @param $prefixNS
     * @param $ucWord
     * @return string
     */
    private function getCachedClassName($serviceName, $cacheKey, $scope, $prefixNS = null, $ucWord = false)
    {
        if (isset($this->refCache[$cacheKey][$serviceName]))
        {
            $class = $this->refCache[$cacheKey][$serviceName];
        }
        else
        {
            $this->refCache[$cacheKey][$serviceName] = $class = $this->getTargetNS($serviceName, $prefixNS, $scope, $ucWord);
        }
        return $class;
    }

    /**
     * @param $serviceName
     * @param $prefixNS
     * @param $scopeName
     * @param $ucWord
     * @return string
     */
    private function getTargetNS($serviceName, $prefixNS = null, $scopeName = null, $ucWord = null)
    {
        if ($prefixNS)
        {
            if ($prefixNS == $this->comApiNS)
            {
                // custom prefixNS is same of comApiNS | e.g. prefixNS = com.youzan, comApiNS = com.youzan
                // do nothing
            }
            else
            {
                if (substr($serviceName, 0, strlen($this->comApiNS)) == $this->comApiNS)
                {
                    // serviceName is prefix with comApiNS -> replace with custom prefixNS
                    $serviceName = $prefixNS . substr($serviceName, strlen($this->comApiNS));
                }
                else
                {
                    // replacement only deal with comApiNS prefix
                    // do nothing
                }
            }
        }

        $parts = explode('.', $serviceName);

        // service-kw replace && upper-case parse
        array_walk($parts, function ($part, $ix) use (&$parts, $scopeName, $ucWord) {
            // service-kw
            if ($part == $this->srvKeyword)
            {
                $parts[$ix] = $scopeName;
            }
            // upper-case word
            if (true === $ucWord)
            {
                $parts[$ix] = ucfirst($parts[$ix]);
            }
        });

        // prepend namespace separator slot
        array_unshift($parts, '');

        // we got full namespace | ^ ^
        return implode('\\', $parts);
    }
}