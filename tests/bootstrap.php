<?php

// register autoloader
use ConstructorOverloading\ConstructorOverloading;
$pathToConstructorOverloading = __DIR__ . '/../src/ConstructorOverloading';
require_once $pathToConstructorOverloading . '/ConstructorOverloading.php';
$constructorOverloading = new ConstructorOverloading();
$constructorOverloading->registerAutoloader();
