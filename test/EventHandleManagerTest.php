<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

class EventHandleManagerTest extends \PHPUnit_Framework_TestCase
{

    private $_eventHandleManager;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        require_once __DIR__ . '/TestClass/ExceptionHandler.php';
        $this->_eventHandleManager = new \point\core\EventHandleManager();
    }

    public function testAddExceptionHandler()
    {
        $this->_eventHandleManager->addExceptionHandler($handler);
    }

    public function testRegister()
    {
        $this->_eventHandleManager->register();
    }
}
