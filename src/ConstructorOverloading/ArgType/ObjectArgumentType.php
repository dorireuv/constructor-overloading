<?php

namespace ConstructorOverloading\ArgType;

/**
 * Object argument type
 */
class ObjectArgumentType extends UnknownArgumentType
{

    /**
     * @var string
     */
    private $className;

    /**
     * Constructor
     *
     * @param string $className class name
     */
    public function __construct($className)
    {
        $this->setClassName($className);
    }

    /**
     * Sets the class name
     *
     * @param string $className class name
     *
     * @return void
     */
    private function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * Gets the class name
     *
     * @return string the class name
     */
    public function getClassName()
    {
        return $this->className;
    }
}
