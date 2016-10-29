<?php

namespace point\core\test;


include_once __DIR__ . '/../Autoloader.php';

class EventHandleManager extends \PHPUnit_Framework_TestCase
{
    public function testAddExceptionHandler()
    {
        require_once __DIR__ . '/TestClass/ExceptionHandler.php';
        $handler = new ExceptionHandler();

        \point\core\EventHandleManager::register();
        \point\core\EventHandleManager::addExceptionHandler($handler);
    }

    public function testRegister()
    {
        require_once __DIR__ . '/TestClass/ExceptionHandler.php';
        \point\core\EventHandleManager::register();
    }
}
