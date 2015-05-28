<?php

namespace point\core\test;

use \point\core\Context;
use \point\core\Bean;

include_once __DIR__ . '/../Autoloader.php';

class ContextTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Foo
     */
    private $_foo;

    public function testGetInstance()
    {
        $context = Context::getInstance();
        $this->assertEquals(get_class($context), 'point\core\Context');
    }

    public function testGetBeanByClassName()
    {

        $context = new Context();

        $context->addConfiguration(array(
            array(
                Bean::CLASS_NAME => '\point\core\test\Foo',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php'
            )
        ));

        $this->_foo = $context->getBeanByClassName('\point\core\test\Foo');
        $hash1 = spl_object_hash($this->_foo);

        $this->_foo = $context->getBeanByClassName('\point\core\test\Foo');
        $hash2 = spl_object_hash($this->_foo);

        $this->assertTrue(is_string($hash1));
        $this->assertTrue(is_string($hash2));
        $this->assertEquals($hash1, $hash2);

    }

    public function testInject()
    {

        $context = new Context();

        $context->addConfiguration(array(
            array(
                Bean::CLASS_NAME => '\point\core\test\Foo',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php'
            ),
            array(
                Bean::CLASS_NAME => '\point\core\test\Inject',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Inject.php'
            )
        ));

        $this->_foo = $context->getBeanByClassName('\point\core\test\Foo');

        $this->assertEquals(get_class($this->_foo->getInject()), 'point\core\test\Inject');
        $this->assertNull($this->_foo->getBar());

    }

    public function testPostInject()
    {

        $context = new Context();

        $context->addConfiguration(array(
            array(
                Bean::CLASS_NAME => '\point\core\test\Foo',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php'
            )
        ));

        $this->_foo = $context->getBeanByClassName('\point\core\test\Foo');

        $this->assertNull($this->_foo->getBar());

        $context->addConfiguration(array(
            array(
                Bean::CLASS_NAME => '\point\core\test\Bar',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php'
            )
        ));

        $this->assertEquals(get_class($this->_foo->getBar()), 'point\core\test\Bar');
    }

    public function testInterfaceInject()
    {

        $context = new Context();

        $context->addConfiguration(array(
            array(
                Bean::CLASS_NAME => '\point\core\test\Foo',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php'
            ),
            array(
                Bean::CLASS_NAME => '\point\core\test\MyInterfaceImp',
                Bean::INCLUDE_PATH => __DIR__ . '/TestClass/MyInterfaceImp.php',
                Bean::AUTO_LOAD => true
            )
        ));

        $this->_foo = $context->getBeanByClassName('\point\core\test\Foo');

        $this->assertEquals(get_class($this->_foo->getInjectInterface()), 'point\core\test\MyInterfaceImp');
    }
}
