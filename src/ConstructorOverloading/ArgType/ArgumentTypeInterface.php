<?php

namespace ConstructorOverloading\ArgType;

/**
 * Argument type interface
 */
interface ArgumentTypeInterface
{

    /**
     * Gets the arg class name
     *
     * @return string the arg class name
     */
    function getClassName();

    /**
     * Gets the distance from the given argument to this
     *
     * @param ArgumentTypeInterface $argumentType argument type
     *
     * @return int -1 if not sub class, otherwise non-negative number of distance
     */
    function getDistanceOf(ArgumentTypeInterface $argumentType);
}
