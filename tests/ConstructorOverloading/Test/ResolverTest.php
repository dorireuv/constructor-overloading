<?php

namespace ConstructorOverloading\Test;

use ConstructorOverloading\Resolver;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * Resolver test
 *
 */
class ResolverTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * Sets up
     */
    protected function setUp()
    {
        $this->setResolver(new Resolver());
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
     * Gets args for which constructor exist
     *
     * @return array
     */
    public function getArgsForWhichConstructorExist()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $int = 0;
        $null = null;
        $float = 0.0;
        $boolean = false;
        $array = array();
        $string = '';
        $resource = fopen('php://stdin', 'r');
        $unknown = fopen('php://stdin', 'r');
        fclose($unknown);

        return array(
            array(array($a, $c), '_constructAC'),
            array(array($a), '_constructA'),
            array(array($b), '_constructB'),
            array(array($c), '_constructC'),
            array(array($int), '_constructInt'),
            array(array($a, $int), '_constructAInt'),
            array(array($array), '_constructArray'),
            array(array($float), '_constructFloat'),
            array(array($null), '_constructNull'),
            array(array($boolean), '_constructBoolean'),
            array(array($resource), '_constructResource'),
            array(array($unknown), '_constructUnknown'),
            array(array($string, $a), '_constructStringOrArrayA'),
            array(array($array, $a), '_constructStringOrArrayA'),
            array(array(), '_constructWithNoParameters'),
        );
    }

    /**
     * Tests the resolve of arguments for which a constructor exist
     *
     * @dataProvider getArgsForWhichConstructorExist
     * @param array $args arguments
     * @param string $expectedConstructorName expected constructor name
     */
    public function testResolveWithArgsForWhichConstructorExist(
        array $args,
        $expectedConstructorName
    ) {
        $resolver = $this->getResolver();
        $overloadedConstructorExampleClass = new OverloadedConstructorExampleClass();
        $constructorName = $resolver->resolve(get_class($overloadedConstructorExampleClass), $args);
        $this->assertEquals($expectedConstructorName, $constructorName);
    }

    /**
     * Gets args for which constructor does not exist
     *
     * @return array
     */
    public function getArgsForConstructorDoesNotExist()
    {
        $a = new A();
        $b = new B();
        $d = new D();
        $int = 0;
        $null = null;
        return array(
            array(array($a, $d)),
            array(array($b, $int)),
            array(array($null, $null)),
        );
    }

    /**
     * Tests the resolve of arguments for which a constructor does not exist
     *
     * @dataProvider getArgsForConstructorDoesNotExist
     * @expectedException RuntimeException
     * @param array $args arguments
     */
    public function testResolveWithArgsForWhichConstructorDoesNotExist(array $args) {
        $resolver = $this->getResolver();
        $overloadedConstructorExampleClass = new OverloadedConstructorExampleClass();
        $resolver->resolve(get_class($overloadedConstructorExampleClass), $args);
    }
}

class A { }
class B { }
class C extends B { }
class D { }

/** @noinspection PhpUndefinedClassInspection */
class OverloadedConstructorExampleClass
{

    /**
     * Doc type hint to a class which does not exist
     *
     * @param G $g
     */
    protected function _constructClassDoesNotExist($g) { }

    protected function _constructAB(A $a, B $b = null) { }

    protected function _constructAC(A $a, C $c = null) { }

    protected function _constructA(A $a) { }

    protected function _constructB(B $b) { }

    protected function _constructC(C $c) { }

    /**
     * Doc type hint for int
     *
     * @param int $int
     */
    protected function _constructInt($int) { }

    /**
     * Doc type hint for integer
     *
     * @param A $a
     * @param integer $integer
     */
    protected function _constructAInt(A $a, $integer) { }

    /**
     * Default value with no doc hint
     */
    protected function _constructString($string = '') { }

    /**
     * Default value with no doc hint
     */
    protected function _constructNull($null = null) { }

    protected function _constructUnknown($unknown) { }

    /**
     * Type hint of array
     */
    protected function _constructArray(array $array) { }

    /**
     * Default value with no doc hint
     */
    protected function _constructFloat($float = 0.0) { }

    /**
     * Default value with no doc hint
     */
    protected function _constructBoolean($boolean = false) { }

    /**
     * @param  resource  $resource
     */
    protected function _constructResource($resource) { }

    /**
     * @param A $a
     * @param string|array $stringOrArray
     */
    protected function _constructStringOrArrayA($stringOrArray, A $a) { }

    protected function _constructWithNoParameters() { }

    protected function doSomething() { }
}
