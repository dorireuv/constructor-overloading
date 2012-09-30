<?php

namespace ConstructorOverloading;

/**
 * Constructor overloading main class
 *
 */
class ConstructorOverloading
{

    /**
     * Tries to autoload a given class name
     *
     * @param string $className class name
     *
     * @return void
     */
    public function autoload($className)
    {
        $prefix = 'ConstructorOverloading';
        if (strpos($className, $prefix) === 0) {
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($className, strlen($prefix) + 1)) . '.php';
            /** @noinspection PhpIncludeInspection */
            require_once __DIR__ . DIRECTORY_SEPARATOR . $filename;
        }
    }

    /**
     * Registers ConstructorOverloading autoloader
     *
     * @return void
     */
    public function registerAutoloader()
    {
        spl_autoload_register(array($this, 'autoload'));
    }
}