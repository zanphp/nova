<?php
/**
 * Spec mgr (for struct)
 * User: moyo
 * Date: 9/25/15
 * Time: 2:31 PM
 */

namespace Kdt\Iron\Nova\Foundation\Traits;

trait StructSpecManager
{
    /**
     * @var array
     */
    public $_TSPEC = [];

    /**
     * @var array
     */
    protected $structSpec = [];

    /**
     * @return array
     */
    public function getStructSpec()
    {
        return $this->structSpec;
    }

    /**
     * @return array
     */
    public function toArray(){
        $structSpec = $this->getStructSpec();
        $arr = [];
        foreach($structSpec as $struct){
            $keyName =  $struct['var'];
            $arr[$keyName] = $this->$keyName;
        }
        return $arr;
    }

    /**
     * for php-ext:thrift-protocol
     */
    private function staticSpecInjecting()
    {
        $this->_TSPEC = $this->structSpec;
    }
}