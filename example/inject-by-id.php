<?php

include_once(__DIR__ . '/../Autoloader.php');

use point\core\Context;
use point\core\Bean;

class Foo
{
    /**
     * @Qualifier("bar.2")
     * @var Bar
     */
    private $_bar;

    public function getBar()
    {
        return $this->_bar;
    }
}

class Bar
{
    private $_name;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function toString()
    {
        return $this->_name;
    }
}

$context = new Context();

$context->addConfiguration(array(
    array(
        Bean::CLASS_NAME => 'Foo'
    ),
    array(
        Bean::CLASS_NAME => 'Bar',
        Bean::ID => 'bar.1',
        Bean::CONSTRUCTOR_ARG => ['i am first.']
    ),
    array(
        Bean::CLASS_NAME => 'Bar',
        Bean::ID => 'bar.2',
        Bean::CONSTRUCTOR_ARG => ['i am second.']
    )
));

$foo = $context->getBeanByClassName('Foo');
var_dump($foo->getBar());  // print Class Bar
