<?php

namespace ConstructorOverloading\ArgType;

use ReflectionClass;

/**
 * Argument type base class
 */
abstract class  ArgumentTypeBase implements ArgumentTypeInterface
{

    /**
     * Gets the arg class name
     *
     * @return string the arg class name
     */
    public function getClassName()
    {
        //return strtolower(explode(' ', preg_replace('/([A-Z][a-z\d]+)([A-Z])/', '\\1 \\2', get_class($this)))[0]);
        return get_class($this);
    }

    /**
     * Gets the distance from the given argument to this
     *
     * @param ArgumentTypeInterface $argumentType argument type
     *
     * @return int -1 if not sub class, otherwise non-negative number of distance
     */
    public function getDistanceOf(ArgumentTypeInterface $argumentType)
    {
        // make sure all classes exist
        $classNames = array(
            $fromClassName = $argumentType->getClassName(),
            $toClassName = $this->getClassName(),
        );
        foreach ($classNames as $className) {
            if (!class_exists($className)) {
                return -1;
            }
        }

        // if class names are equal then no distance
        if ($fromClassName === $toClassName) {
            return 0;
        }

        $argTypeReflectionClass = new ReflectionClass($fromClassName);
        $isSubclass = $argTypeReflectionClass->isSubclassOf($toClassName);
        if (!$isSubclass) {
            return -1;
        }

        $distance = 0;
        $parent = $argTypeReflectionClass;
        while ($parent->getName() !== $toClassName) {
            $distance++;
            $parent = $parent->getParentClass();
        }

        return $distance;
    }
}
