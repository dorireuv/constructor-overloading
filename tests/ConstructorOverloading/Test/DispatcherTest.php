<?php

namespace ConstructorOverloading\Test;

use ConstructorOverloading\Dispatcher;
use ConstructorOverloading\Resolver;
use PHPUnit_Framework_TestCase;

/**
 * Dispatcher test
 *
 */
class DispatcherTest extends PHPUnit_Framework_TestCase
{

    /**
     * Tests the dispatch
     *
     */
    public function testDispatch()
    {
        $tempObject = new TempObject();
        $args = array();

        $resolverMock = $this->getMock('ConstructorOverloading\\Resolver');
        $resolverMock->expects($this->once())
            ->method('resolve')
            ->with(get_class($tempObject), $args)
            ->will($this->returnValue('_construct1'));

        /** @var $resolver Resolver */
        $resolver = $resolverMock;
        $dispatcher = new Dispatcher($resolver);
        $dispatcher->dispatch($tempObject, $args);
    }

    /**
     * Tests the dispatcher constructor
     *
     */
    public function testDispatcherConstructor()
    {
        new Dispatcher();

        /** @var $resolver Resolver */
        $resolverMock = $this->getMock('ConstructorOverloading\\Resolver');
        $resolver = $resolverMock;
        new Dispatcher($resolver);
    }
}

class TempObject
{
    protected function _construct1() { }
}
