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

    protected function structConvertDb(TStruct $tStruct, array $dbMap){
        $structMap = $tStruct->toArray();

        $record = [];
        foreach($dbMap as $dbField => $structField){
            if(isset($structMap[$structField])){
                $record[$dbField] = $structMap[$structField];
            }
        }
        return $record;
    }

    protected function dbConvertStruct(TStruct $sStruct,array $dbMap,array $data){
        foreach($dbMap as $dbField => $structField){
            if(isset($sStruct->$structField) && isset($data[$dbField])){
                $sStruct->$structField = $data[$dbField];
            }
        }
        return $sStruct;
    }

    /**
     * for php-ext:thrift-protocol
     */
    private function staticSpecInjecting()
    {
        $this->_TSPEC = $this->structSpec;
    }
}