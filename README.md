# point-core [![Build Status](https://travis-ci.org/samejack/point-core.svg?branch=master)](https://travis-ci.org/samejack/point-core) [![Coverage Status](https://coveralls.io/repos/samejack/point-core/badge.svg?branch=master)](https://coveralls.io/r/samejack/point-core?branch=master)

## Overview
This is a PHP IoC/DI Module Container.It can inject instance of object through the @Annotation on PHPDoc.

## Features
* Dependency Injection
* Inversion of Control
* File/Class Lazy Loading
* Configuration by PHP Annotation
* Rich Module Framework

## Annotaion Specification
| Annotation          | Description                |
| :-------------      | :-------------             |
| @Autowired          | Auto inject                |
| @var                | Mapping by Class or Interface |
| @Qualifier          | Inject by identify         |

## Bean Configuration
| Configuration       | Description                |
| :-------------      | :-------------             |
| Bean::INIT-METHOD         | Initialize invoke function |
| Bean::SCOPE               | Instance mode (prototype or singleton) |
| Bean::CONSTRUCTOR_ARG     | Constructor argunet        |
| Bean::PROPERTY            | Set default property       |
| Bean::AUTO_LOAD           | Auto load class            |
| Bean::INCLUDE_PATH        | Auto include file path     |


PHP Example:
```php
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
```

## License
Apache License 2.0
