<?php

namespace ConstructorOverloading;

use ReflectionClass;
use RuntimeException;

/**
 * Dispatcher
 *
 */
class Dispatcher
{

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * Constructor
     *
     * @param Resolver $resolver resolver
     *
     */
    public function __construct(Resolver $resolver = null)
    {
        if (!isset($resolver)) {
            $resolver = new Resolver();
        }
        $this->setResolver($resolver);
    }

    /**
     * Sets the resolver
     *
     * @param Resolver $resolver the resolver
     *
     * @return void
     */
    private function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Gets the resolver
     *
     * @return Resolver the resolver
     */
    private function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Dispatches the constructor for the given arguments
     *
     * @param object $object object
     * @param array $args arguments
     *
     * @return void
     *
     * @throws RuntimeException when no constructor found
     */
    public function dispatch($object, array $args)
    {
        $resolver = $this->getResolver();
        $constructorName = $resolver->resolve(get_class($object), $args);
        $reflectionClass = new ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod($constructorName);
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invokeArgs($object, array($args));
        $reflectionMethod->setAccessible(false);
    }
}
