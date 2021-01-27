<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class ArrayUtility
 *
 * @package CarstenWalther\XliffGen\Utility
 */
class ArrayUtility
{
    /**
     * @param $object
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    public static function objectToArray($object) : array
    {
        if (is_object($object)) {
            $object = self::dismount($object);
        }
        if (is_array($object)) {
            $new = [];
            foreach ($object as $key => $value) {
                $new[$key] = self::objectToArray($value);
            }
        } else {
            $new[] = $object;
        }
        return $new;
    }

    /**
     * @param $object
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function dismount($object) : array
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        $array = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }
        return $array;
    }

    /**
     * @param string $needle
     * @param array  $haystack
     *
     * @return mixed
     */
    public static function arraySearchRecursive(string $needle, array $haystack)
    {
        foreach ($haystack as $key => $value) {
            if ($needle === $value) {
                return [$key];
            }

            if (is_array($value) && $subkey = self::arraySearchRecursive($needle, $value)) {
                array_unshift($subkey, $key);
                return $subkey;
            }
        }
    }
}
