# point-core [![Build Status](https://travis-ci.org/samejack/point-core.svg?branch=master)](https://travis-ci.org/samejack/point-core)

## Overview
This is a PHP IoC/DI Module Container.It can inject instance of object through the @Annotation on PHPDoc.

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
