<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TType;

class StructValidator
{
    /**
     * 验证接收参数required字段
     * @param string $serviceName
     * @param string $methodName
     * @param array $args
     * @param array $inputStruct
     */
    public static function validateInput($serviceName, $methodName, array $args, array $inputStruct)
    {
        foreach ($inputStruct as $pos => $spec) {
            $path = "$serviceName::{$methodName} arguments >-> [{$spec['var']}]";

            if (isset($args[$pos-1])) {
                static::validateHelper($args[$pos-1], $spec, $path);
            } else {
                if (isset($subSpec["required"])) {
                    static::validateFail($path);
                }
            }
        }
    }

    /**
     * 验证返回值required字段
     * @param array $retStruct
     */
    public static function validateOutput(array $retStruct)
    {
        foreach ($retStruct as $pos => $spec) {
            if ($spec["value"] !== null) {
                static::validateHelper($spec["value"], $spec, "return_value >-> {$spec["var"]}");
            }
        }
    }

    /**
     * @param array $argVal
     * @param array $spec
     * @param string $path
     */
    private static function validateHelper($argVal, $spec, $path)
    {
        switch ($spec["type"]) {

            case TType::STRUCT:
                if (!method_exists($argVal, "getStructSpec")) {
                    continue; // or throw ?!
                }

                /* @var StructSpecManager $argVal */
                foreach ($argVal->getStructSpec() as $subPos => $subSpec) {
                    if (isset($argVal->$subSpec["var"])) {
                        static::validateHelper($argVal->$subSpec["var"], $subSpec, "$path.{$subSpec["var"]}");
                        continue;
                    } else {
                        if (isset($subSpec["required"])) {
                            static::validateFail("$path.{$subSpec["var"]}");
                        }
                    }
                }
                break;

            case TType::MAP:
                foreach ($argVal as $key => $arg) {
                    static::validateHelper($key, $spec["key"], "{$path}.<$key>");
                    static::validateHelper($arg, $spec["val"], "{$path}.$key");
                }
                break;

            case TType::SET:
                foreach ($argVal as $i => $arg) {
                    static::validateHelper($arg, $spec["elem"], "{$path}[$i]");
                }
                break;

            case TType::LST:
                foreach ($argVal as $i => $arg) {
                    static::validateHelper($arg, $spec["elem"], "{$path}[$i]");
                }
                break;

            default:
                if (isset($spec["required"]) && $argVal === null) {
                    static::validateFail($path);
                }
        }
    }

    private static function validateFail($path)
    {
        throw new TApplicationException("Validate fail, required: \"$path\" )", 500); // TODO code
    }
}