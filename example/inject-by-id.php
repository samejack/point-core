<?php

include_once(__DIR__ . '/../Autoloader.php');

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
