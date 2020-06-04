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

    /**
     * @var array
     */
    private $_errorHandlers = array();

    /**
     * @Autowired
     * @var \point\core\Framework
     */
    private $_framework;

    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true, false);
        set_exception_handler(array($this, 'exceptionHandler'));

        if (!is_null($this->_framework) && isset($this->_framework->getConfig()['displayErrorLevel'])) {
            set_error_handler(array($this, 'errorHandler'), $this->_framework->getConfig()['displayErrorLevel']);
        } else {
            set_error_handler(array($this, 'errorHandler'));
        }
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
     * Master exception handler
     *
     * @param \Exception $exception
     * @return boolean
     */
    public function errorHandler($code, $message, $file, $line)
    {
        foreach ($this->_errorHandlers as &$errorHandler) {
            if ($errorHandler->errorHandler($code, $message, $file, $line) === true) {
                break;
            }
        }
    }

    /**
     * Add an exception handler into framework
     *
     * @param object $handler
     */
    public function addExceptionHandler(&$handler)
    {
        array_push($this->_exceptionHandlers, $handler);
    }

    /**
     * Add an error handler into framework
     *
     * @param object $handler
     */
    public function addErrorHandler(&$handler)
    {
        array_push($this->_errorHandlers, $handler);
    }
}
