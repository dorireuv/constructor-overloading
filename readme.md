Constructor Overloading
=======================
A library which allows you to have constructor overloading in PHP >=5.3 projects.
This is simply done using: type hint, default value and PHP doc.

Usage
-----
In order to use the library it's best recommended to follow some rules. The library
is looking for any private / protected methods width start with _construct in your class. Among
these methods the best candidate is picked up. In order to help the library to find the best
candidate you shall follow these rules:

1. If your argument is an object, use type hint (example: function(A $a)).
2. Otherwise, the argument is a primitive and in this case if it has a default value, then
you can use it to hint its type (example: function ($int = 0)).
3. Otherwise you can use PHP doc (example: @param int $int)

** In case of a primitive has default value and PHP doc the default value will be preferred over the PHP doc.

Examples
--------
First, in order to use the library, one must register the autoloader:

    <?php

    use ConstructorOverloading\ConstructorOverloading;
    $pathToConstructorOverloading = __DIR__ . '/ConstructorOverloading';
    require_once $pathToConstructorOverloading . '/ConstructorOverloading.php';
    $constructorOverloading = new ConstructorOverloading();
    $constructorOverloading->registerAutoloader();

Then, let's say you have a class called Rectangle which can be constructed by
either width and height or by size (square):

    <?php

    use ConstructorOverloading\Dispatcher;

    class Rectangle
    {
        private $width, $height;

        public function __construct()
        {
            $dispatcher = new Dispatcher();
            $dispatcher->dispatch($this, func_get_args());
        }

        /**
         * @param int $width
         * @param int $height
         */
        protected function _constructWidthAndHeight($width, $height)
        {
            $this->width = $width;
            $this->height = $height;
        }

        /**
         * @param int $size
         */
        protected function _constructSize($size)
        {
            $this->width = $this->height = $size;
        }
    }

Now, if you use:

    $rectangle = new Rectangle(10, 20);

The _constructWidthAndHeight will be invoked and result in width = 10, height = 20

And, if you use:

    $rectangle = new Rectangle(30);

The _constructSize will be invoked and result in width = height = 30
