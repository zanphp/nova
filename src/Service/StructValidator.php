<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TType;

class StructValidator
{
    public static $outputIgnoreValidVar = ["success", "novaNull", "novaEmptyList"];

    /**
     * client: 验证请求参数required字段
     * server: 验证接收参数required字段
     * @param array $args
     * @param array $inputStruct
     */
    public static function validateInput(array $args, array $inputStruct)
    {
        foreach ($inputStruct as $pos => $spec) {
            $path = "input value [{$spec['var']}]";

            if (isset($args[$spec["var"]])) {
                static::validateHelper($args[$spec["var"]], $spec, $path);
            } /*else if (isset($args[$pos-1])) {
                static::validateHelper($args[$pos-1], $spec, $path);
            } */else {
                if (isset($subSpec["required"])) {
                    static::validateFail("$path is null");
                }
            }
        }
    }

    /**
     * client: 验证请求参数required字段
     * server: 验证接受参数required字段
     * @param array $outputStruct
     */
    public static function validateOutput(array $outputStruct)
    {
        foreach ($outputStruct as $pos => $spec) {
            if ($spec["value"] !== null) {
                static::validateHelper($spec["value"], $spec, "output -> {$spec["var"]}");
            } else {
                if (in_array($spec["var"], static::$outputIgnoreValidVar, true)) {
                    continue;
                }
                static::validateFail("output value is null: " . json_encode($spec, JSON_PRETTY_PRINT));
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
                            static::validateFail("$path.{$subSpec["var"]} is null");
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
                    static::validateFail("$path is null");
                }
        }
    }

    private static function validateFail($desc)
    {
        throw new TApplicationException("nova validate fail, $desc", 500); // TODO code
    }
}