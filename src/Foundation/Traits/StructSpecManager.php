<?php
/**
 * Spec mgr (for struct)
 * User: moyo
 * Date: 9/25/15
 * Time: 2:31 PM
 */

namespace Kdt\Iron\Nova\Foundation\Traits;
use Kdt\Iron\Nova\Foundation\Protocol\TStruct;
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

    public function structConvertDb( array $dbMap){
        $structMap = $this->toArray();

        $record = [];
        foreach($dbMap as $dbField => $structField){
            if(isset($structMap[$structField])){
                $record[$dbField] = $structMap[$structField];
            }
        }
        return $record;
    }

    public function dbConvertStruct(array $dbMap,array $data){
        foreach($dbMap as $dbField => $structField){
            if(isset($this->$structField) && isset($data[$dbField])){
                $this->$structField = $data[$dbField];
            }
        }
        return $this;
    }

    /**
     * for php-ext:thrift-protocol
     */
    private function staticSpecInjecting()
    {
        $this->_TSPEC = $this->structSpec;
    }
}