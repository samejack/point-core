<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\ApplicationContext;
use \point\core\Bean;
use \point\core\BeanFactory;

class BeanFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {

        $applicationContext = new ApplicationContext();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php'
        );

        $beanFactory = new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertNull($bar->getData());
        $this->assertFalse($bar->autoInit);

    }

    public function testGetInstanceInitMethod()
    {

        $applicationContext = new ApplicationContext();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInit')
        );

        $beanFactory = new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertNull($bar->getData());
        $this->assertTrue($bar->autoInit);
    }

    public function testGetInstanceInitMethodWithParameter()
    {

        $applicationContext = new ApplicationContext();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Bar',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Bar.php',
            Bean::INIT_METHOD => array('autoInit', 'setData' => array('Test_Input_Parameter'))
        );

        $beanFactory = new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );
        $bar = $beanFactory->getInstance();

        $this->assertEquals($bar->getData(), $config[Bean::INIT_METHOD]['setData'][0]);
        $this->assertTrue($bar->autoInit);
    }

    public function testAutoload()
    {

        $applicationContext = new ApplicationContext();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\NonAutoload',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/NonAutoload.php'
        );

        include_once __DIR__ . '/TestClass/NonAutoload.php';

        new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );

        $this->assertNull(NonAutoload::$INSTANCE);

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Autoload',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Autoload.php',
            Bean::AUTO_LOAD => true
        );

        include_once __DIR__ . '/TestClass/Autoload.php';

        new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );

        $this->assertNotNull(Autoload::$INSTANCE);
    }

    public function testPropertySet()
    {

        $applicationContext = new ApplicationContext();

        $config = array(
            Bean::CLASS_NAME => '\point\core\test\Property',
            Bean::INCLUDE_PATH => __DIR__ . '/TestClass/Property.php',
            Bean::PROPERTY => array (
                'priVar' => 'INJECT_STRING_1',
                'pubVar' => 'INJECT_STRING_2'
            )
        );

        $beanFactory = new BeanFactory(
            $applicationContext,
            $config[Bean::CLASS_NAME],
            $config
        );

        $property = $beanFactory->getInstance();

        $this->assertEquals($property->getVars()['_priVar'], $config[Bean::PROPERTY]['priVar']);
        $this->assertEquals($property->getVars()['pubVar'], $config[Bean::PROPERTY]['pubVar']);

    }
}
