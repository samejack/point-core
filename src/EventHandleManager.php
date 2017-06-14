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
        set_exception_handler(array($this, 'exceptionHandler'));
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
     * Master exception handler
     *
     * @param \Exception $exception
     * @return boolean
     */
    public function exceptionHandler($exception)
    {
        foreach ($this->_exceptionHandlers as &$exceptionHandler) {
            if ($exceptionHandler->exceptionHandler($exception) === true) {
                break;
            }
        }
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
