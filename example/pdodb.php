<?php

include_once(__DIR__ . '/../Autoloader.php');

use point\core\Context;
use point\core\Bean;

class MyControllerA
{
    /**
    * @Autowired
    * @var PDO
    */
    private $_pdo;

    public function getPdo()
    {
        return $this->_pdo;
    }
}

class MyControllerB
{
    /**
    * @Autowired
    * @var PDO
    */
    private $_pdo;

    public function getPdo()
    {
        return $this->_pdo;
    }
}

$context = new Context();

$context->addConfiguration(array(
    array(
        Bean::CLASS_NAME => 'MyControllerA'
    ),
    array(
        Bean::CLASS_NAME => 'MyControllerB'
    ),
    array(
        Bean::CLASS_NAME => 'PDO',
        Bean::CONSTRUCTOR_ARG => ['mysql:host=localhost;dbname=mysql', 'root', 'password!']
    )
));

$ctrlA = $context->getBeanByClassName('MyControllerA');

var_dump($ctrlA->getPdo());  // print: class PDO#11 (0)...

$ctrlB = $context->getBeanByClassName('MyControllerB');

var_dump($ctrlB->getPdo());  // print: class PDO#11 (0)...