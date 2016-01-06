<?php
/**
 * Service convert (IO format)
 * User: moyo
 * Date: 9/25/15
 * Time: 2:49 PM
 */

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Support\Str;
use Thrift\Type\TType;

class Convert
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @param $data
     * @param $struct
     * @return array
     */
    public function inputArgsToFuncArray($data, $struct)
    {
        $pack = [];
        foreach ($struct as $pos => $config)
        {
            $key = $config['var'];
            if (isset($data[$key]))
            {
                $pack[] = $this->outputArrayToStruct($data[$key], $config);
            }
            else
            {
                $pack[] = null;
            }
        }
        return $pack;
    }

    /**
     * @param $data
     * @return array
     */
    public function inputArgsToIronInput($data)
    {
        $pack = [];
        foreach ($data as $key => $value)
        {
            $pack[Str::snake($key)] = $this->outputStructToArray($value);
        }
        return [$pack];
    }

    /**
     * @param $instance
     * @return array
     */
    public function outputStructToArray($instance)
    {
        $result = null;
        switch (gettype($instance))
        {
            case 'object':
                $result = [];
                $vars = get_object_vars($instance);
                foreach ($vars as $key => $val)
                {
                    $result[Str::snake($key)] = $this->outputStructToArray($val);
                }
                break;
            case 'array':
                $result = [];
                foreach ($instance as $key => $data)
                {
                    $result[$key] = $this->outputStructToArray($data);
                }
                break;
            default:
                $result = $instance;
        }
        return $result;
    }

    /**
     * @param $data
     * @param $struct
     * @return mixed
     */
    public function outputArrayToStruct($data, $struct)
    {
        $result = null;
        if ($data)
        {
            switch ($struct['type'])
            {
                case TType::LST:
                    $result = [];
                    foreach ($data as $pos => $val)
                    {
                        $result[$pos] = $this->outputArrayToStruct($val, $struct['elem']);
                    }
                    break;
                case TType::STRUCT:
                    /**
                     * @var \Kdt\Iron\Nova\Foundation\Protocol\TStruct
                     */
                    $objIns = new $struct['class'];
                    $objStruct = $objIns->getStructSpec();
                    foreach ($objStruct as $field)
                    {
                        $vkObject = $field['var'];
                        if (property_exists($struct['class'], $vkObject) && property_exists($data, $vkObject))
                        {
                            $objIns->$vkObject = $this->outputArrayToStruct($data->$vkObject, $field);
                        }
                    }
                    $result = $objIns;
                    break;
                default:
                    $result = $data;
            }
        }
        return $result;
    }
}