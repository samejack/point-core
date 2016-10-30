<?php

namespace point\core;

/**
 * Class EventHandleManager
 *
 * @author sj
 */
class EventHandleManager
{
    /**
     * @var array
     */
    private $_classLoaders = array();

    /**
     * @var array
     */
    private $_exceptionHandlers = array();

    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true, false);
        //TODO void handler ( Throwable $ex ) for PHP 7
    }

    /**
     * Add an class loader into list
     *
     * @param object $classLoader
     */
    public function addClassLoader(&$classLoader)
    {
        array_push($this->_classLoaders, $classLoader);
    }

    /**
     * Class auto load
     *
     * @param String $className
     * @return boolean
     */
    public function loadClass($className)
    {
        foreach ($this->_classLoaders as &$classLoader) {
            if ($classLoader->loadClass($className)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add an exception handler into list
     *
     * @param object $handler
     */
    public function addExceptionHandler(&$handler)
    {
        array_push($this->_exceptionHandlers, $handler);
    }

}
