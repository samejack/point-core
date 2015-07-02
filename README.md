# point-core [![Build Status](https://travis-ci.org/samejack/point-core.svg?branch=master)](https://travis-ci.org/samejack/point-core) [![Code Climate](https://codeclimate.com/github/samejack/point-core/badges/gpa.svg)](https://codeclimate.com/github/samejack/point-core) [![Test Coverage](https://codeclimate.com/github/samejack/point-core/badges/coverage.svg)](https://codeclimate.com/github/samejack/point-core/coverage) [![Coverage Status](https://coveralls.io/repos/samejack/point-core/badge.svg?branch=master)](https://coveralls.io/r/samejack/point-core?branch=master)

## Overview
This is a PHP IoC/DI Module Container.It can inject instance of object through the @Annotation on PHPDoc.

## Features
* Dependency injection
* Inversion of control
* Lazy loading
* Annotation config

## Annotaion Specification
| Annotation          | Description                |
| :-------------      | :-------------             |
| @Autowired          | Auto inject                |
| @var                | Mapping by Class or Interface |
| @Qualifier          | Inject by identify         |

## Bean Configuration
| Configuration       | Description                |
| :-------------      | :-------------             |
| INIT-METHOD         | Initialize invoke function |
| SCOPE               | Instance mode (prototype or singleton) |
| CONSTRUCTOR_ARG     | Constructor argunet        |
| PROPERTY            | Set default property       |
| AUTO_LOAD           | Auto load class            |
| INCLUDE_PATH        | Auto include file path     |


example:

  
    <?php
    
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
    echo get_object($foo->getBar());  // print Bar

## License
Apache License 2.0
