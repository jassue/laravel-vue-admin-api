<?php

namespace App\Domain\Common\Enum;

use Exception;

abstract class BaseEnum
{
    public $name;
    public $value;

    /**
     * BaseEnum constructor.
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @param $value
     * @return BaseEnum|null
     */
    public static function byValue($value)
    {
        try {
            $enumClass = new \ReflectionClass(get_called_class());
        } catch (\ReflectionException $e) {
            return null;
        }
        $constants = collect($enumClass->getConstants())->flip();
        if ($constants->has($value))
            return new static($constants->all()[$value], $value);
        return null;
    }

    /**
     * @param $methodName
     * @param $argument
     * @return BaseEnum|null
     */
    public static function __callStatic($methodName, $argument)
    {
        try {
            $value = constant('static::' . $methodName);
        } catch (Exception $e) {
            return null;
        }
        return new static($methodName, $value);
    }

    /**
     * @return array
     */
    public static function getConstants()
    {
        try {
            $enumClass = new \ReflectionClass(get_called_class());
        } catch (\ReflectionException $e) {
            return [];
        }
        return $enumClass->getConstants();
    }
}
