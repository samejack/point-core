# point-core
[![Latest Stable Version](https://poser.pugx.org/samejack/point-core/v/stable)](https://packagist.org/packages/samejack/point-core)
[![Build Status](https://travis-ci.org/samejack/point-core.svg?branch=master)](https://travis-ci.org/samejack/point-core)
[![Coverage Status](https://coveralls.io/repos/samejack/point-core/badge.svg?branch=master)](https://coveralls.io/r/samejack/point-core?branch=master) 

## Overview
This is a PHP IoC/DI Module Container.It can inject instance of object through the @Annotation on PHPDoc.

## Features
* Dependency Injection
* Inversion of Control
* File/Class Lazy Loading
* Configuration by PHP Annotation
* Rich Module Framework

## Getting and Start (composer)

### Install point-core from composer repository
```shell
composer require samejack/point-core
```

### Get first object from context
```php
<?php

include 'vendor/autoload.php';

use point\core\Context;
use point\core\Bean;

class Bar
{
  public function __toString()
  {
    return 'Bar!';
  }
}

$context = new Context();

$context->addConfiguration(array(
  array(
    Bean::CLASS_NAME => 'Bar'
  )
));

$bar = $context->getBeanByClassName('Bar');
echo $bar;  // print Bar!
```

## Annotaion Specification
| Annotation          | Description                |
| :-------------      | :-------------             |
| @Autowired          | Auto inject                |
| @Autoload           | Auto load class file without bean defined |
| @var                | Mapping by class or interface name |
| @Qualifier          | Inject by identify         |

## Bean Configuration
| Configuration       | Description                | Optional        |
| :-------------      | :-------------             | :------------   |
| Bean::CLASS_NAME          | Class name of bean                     |   |
| Bean::ID                  | Inject object by ID via @Qualifier     | V |
| Bean::INIT-METHOD         | Initialize invoke function             | V |
| Bean::SCOPE               | Instance mode (prototype or singleton) | V |
| Bean::CONSTRUCTOR_ARG     | Constructor arguments                  | V |
| Bean::PROPERTY            | Set default property                   | V |
| Bean::AUTO_LOAD           | Auto load class when be dependent on   | V |
| Bean::INCLUDE_PATH        | Auto include file path (use context autoload before SPL) | V |

## PHP Example (Snippet Code):
### General Inject
```php
<?php

use point\core\Context;
use point\core\Bean;

class Foo
{
  /**
   * @Autowired
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
}

$context = new Context();

$context->addConfiguration(array(
  array(
    Bean::CLASS_NAME => 'Foo'
  ),
  array(
    Bean::CLASS_NAME => 'Bar'
  )
));

$foo = $context->getBeanByClassName('Foo');
var_dump($foo->getBar());  // print Class Bar
```

### Inject After
```php
<?php

use point\core\Context;
use point\core\Bean;

class Foo
{
  /**
   * @Autowired
   * @var Bar
   */
  private $_bar;

  public function getBar()
  {
      return $this->_bar;
  }
}

$context = new Context();

$context->addConfiguration(array(
  array(
    Bean::CLASS_NAME => 'Foo'
  )
));

$foo = $context->getBeanByClassName('Foo');

var_dump($foo->getBar());  // print NULL on unload Bar Class

// load Bar class
class Bar
{
}

// set configuration
$context->addConfiguration(array(
    array(
        Bean::CLASS_NAME => 'Bar'
    )
));

var_dump($foo->getBar());  // print Class Bar
```

### Inject by ID of Bean
```php

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
```

### PDODB inject example
```php
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
```

## License
Apache License 2.0
