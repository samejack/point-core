<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Context;
use \point\core\Bean;
use \point\core\BeanFactory;

class BeanFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {

        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php'
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertNull($bar->getData());
        $this->assertFalse($bar->autoInit);

    }

    public function testGetInstanceInitMethod()
    {

        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInit')
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertNull($bar->getData());
        $this->assertTrue($bar->autoInit);

        // error
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInitFail')
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        try {
            $beanFactory->getInstance();
        } catch (\Exception $exception) {
            $this->assertEquals(get_class($exception), 'Exception');
        }
    }

    public function testGetInstanceInitMethodWithParameter()
    {
        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInit', 'setData' => array('Test_Input_Parameter'))
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertEquals($bar->getData(), $config[Bean::INIT_METHOD]['setData'][0]);
        $this->assertTrue($bar->autoInit);
    }

    public function testAutoload()
    {

        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\NonAutoload',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/NonAutoload.php'
        );

        include_once __DIR__ . '/TestClass/NonAutoload.php';

        new BeanFactory(
            $context,
            $config
        );

        $this->assertNull(NonAutoload::$INSTANCE);

        new NonAutoload();
        $this->assertNotNull(NonAutoload::$INSTANCE);

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Autoload',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Autoload.php',
            Bean::AUTO_LOAD => true
        );

        include_once __DIR__ . '/TestClass/Autoload.php';

        new BeanFactory(
            $context,
            $config
        );

        $this->assertNotNull(Autoload::$INSTANCE);
    }

    public function testGetReflectionClass()
    {
        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInit', 'setData' => array('Test_Input_Parameter'))
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        $reflectionClass = $beanFactory->getReflectionClass();

        $this->assertEquals($reflectionClass, null);
    }

    public function testPropertySet()
    {
        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Property',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Property.php',
            Bean::PROPERTY => array (
                '_priVar'       => 'INJECT_STRING_1',
                'pubVar'        => 'INJECT_STRING_2',
                'autoUnderLine' => 'INJECT_STRING_3',
                '_protectedVar' => 'INJECT_STRING_4',
            )
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $property = $beanFactory->getInstance();

        $vars = $property->getVars();
        $this->assertEquals($vars['_priVar'], $config[Bean::PROPERTY]['_priVar']);
        $this->assertEquals($vars['pubVar'], $config[Bean::PROPERTY]['pubVar']);
        $this->assertEquals($vars['_autoUnderLine'], $config[Bean::PROPERTY]['autoUnderLine']);
        $this->assertEquals($vars['_protectedVar'], $config[Bean::PROPERTY]['_protectedVar']);
    }

    public function testPropertySetException()
    {
        $context = new Context();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Property',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Property.php',
            Bean::PROPERTY => array (
                'priVar' => array()
            )
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $catchException = null;
        try {
            $beanFactory->getInstance();
        } catch (\Exception $exception) {
            $catchException = $exception;
        }

        $this->assertEquals($catchException->getMessage(), 'Property value not a string.');
    }

    public function testScopeSingleton()
    {
        // scope=(default)
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Foo',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php'
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $hash1 = spl_object_hash($beanFactory->getInstance());
        $hash2 = spl_object_hash($beanFactory->getInstance());

        $this->assertTrue(is_string($hash1));
        $this->assertTrue(is_string($hash2));
        $this->assertEquals($hash1, $hash2);

        // scope=singleton
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Foo',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php',
            Bean::SCOPE => Bean::SCOPE_SINGLETON
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $hash1 = spl_object_hash($beanFactory->getInstance());
        $hash2 = spl_object_hash($beanFactory->getInstance());

        $this->assertTrue(is_string($hash1));
        $this->assertTrue(is_string($hash2));
        $this->assertEquals($hash1, $hash2);
    }

    public function testScopePrototype()
    {
        // scope=prototype
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Foo',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Foo.php',
            Bean::SCOPE => Bean::SCOPE_PROTOTYPE
        );

        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $hash1 = spl_object_hash($beanFactory->getInstance());
        $hash2 = spl_object_hash($beanFactory->getInstance());

        $this->assertTrue(is_string($hash1));
        $this->assertTrue(is_string($hash2));
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testConstructorArgs()
    {
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\ConstructorArgs',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/ConstructorArgs.php',
            Bean::CONSTRUCTOR_ARG => array('STRING', array('MY', 'NAME'))
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $this->assertEquals($beanFactory->getInstance()->arg1, $config[Bean::CONSTRUCTOR_ARG][0]);
        $this->assertEquals($beanFactory->getInstance()->arg2[0], $config[Bean::CONSTRUCTOR_ARG][1][0]);
        $this->assertEquals($beanFactory->getInstance()->arg2[1], $config[Bean::CONSTRUCTOR_ARG][1][1]);
    }

    public function testConstructorArgsException()
    {
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\ConstructorArgs',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/ConstructorArgs.php',
            Bean::CONSTRUCTOR_ARG => 'GG!!'
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $catchException = null;
        try {
            $beanFactory->getInstance();
        } catch (\Exception $exception) {
            $catchException = $exception;
        }
        $this->assertEquals($catchException->getMessage(), 'CONSTRUCTOR_ARG not a array.');
    }

    public function testHasInstance()
    {
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\ConstructorArgs',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/ConstructorArgs.php',
            Bean::CONSTRUCTOR_ARG => array('STRING', array('MY', 'NAME'))
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );

        $this->assertFalse($beanFactory->hasInstanced());
        $beanFactory->getInstance();
        $this->assertTrue($beanFactory->hasInstanced());
    }

    public function testSetConfiguration()
    {
        $context = new Context();
        $config = array(
            Bean::CLASS_NAME => '\point\core\test\ConstructorArgs',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/ConstructorArgs.php',
            Bean::CONSTRUCTOR_ARG => array('STRING', array('MY', 'NAME'))
        );
        $beanFactory = new BeanFactory(
            $context,
            $config
        );
        try {
            $beanFactory->setConfiguration($config);
        } catch (\Exception $exception) {
            $this->assertEquals(get_class($exception), 'Exception');
        }

        $errorConfig = array();
        $beanFactory = new BeanFactory(
            $context
        );
        try {
            $beanFactory->setConfiguration($errorConfig);
        } catch (\Exception $exception) {
            $this->assertEquals(get_class($exception), 'Exception');
        }

    }
}
