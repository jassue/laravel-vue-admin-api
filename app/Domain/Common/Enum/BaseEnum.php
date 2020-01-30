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
     * @throws \ReflectionException
     */
    public static function byValue($value)
    {
        $enumClass = new \ReflectionClass(get_called_class());
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
     * @throws \ReflectionException
     */
    public static function getConstants()
    {
        $enumClass = new \ReflectionClass(get_called_class());
        return $enumClass->getConstants();
    }
}
