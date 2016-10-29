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
    private static $_classLoaders = array();

    /**
     * @var array
     */
    private static $_exceptionHandlers = array();

    public static function register()
    {
        spl_autoload_register('point\core\EventHandleManager::loadClass', true, false);
        set_exception_handler('point\core\EventHandleManager::fireExceptionHandler');
    }

    /**
     * Add an class loader into list
     *
     * @param object $classLoader
     */
    public static function addClassLoader(&$classLoader)
    {
        array_push(self::$_classLoaders, $classLoader);
    }

    /**
     * Class auto load
     *
     * @param String $className
     * @return boolean
     */
    public static function loadClass($className)
    {
        foreach (self::$_classLoaders as &$classLoader) {
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
    public static function addExceptionHandler(&$handler)
    {
        array_push(self::$_exceptionHandlers, $handler);
    }

    /**
     * Exception handler
     *
     * @param \Exception $exception
     * @return boolean
     */
    public static function fireExceptionHandler(\Exception &$exception)
    {
        if (count(self::$_exceptionHandlers) > 0) {
            foreach (self::$_exceptionHandlers as &$handler) {
                if (method_exists($handler, 'exceptionHandler') && $handler->exceptionHandler($exception)) {
                    break;
                }
            }
        } else {
            echo $exception;
        }
        return false;
    }
}
