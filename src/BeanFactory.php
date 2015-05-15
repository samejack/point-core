<?php

namespace point\core;

/**
 * Class BeanFactory
 *
 * @author sj
 */
class BeanFactory
{

    /**
     * Hash key
     * @var string
     */
    private static $_KEY_R_BEAN = 'KEY_R_BEAN';
    private static $_KEY_R_PROPERTY = 'KEY_R_PROPERTY';

    private $_className;
    private $_instance = null;
    private $_initMethods = array();
    private $_dependencyInjectionBeans = array();
    private $_hasConfiguration = false;

    /**
     * @var ReflectionClass
     */
    private $_reflector = null;

    /**
     * @var Array
     */
    private $_rawConfiguration;

    /**
     * @var ApplicationContext
     */
    private $_applicationContext;

    /**
     * Construct
     *
     * @param ApplicationContext $applicationContext ApplicationContext
     * @param string             $className          Class or Interface Name
     * @param array              $configuration      Bean Configuration
     * @param \ReflectionClass   $reflector
     */
    public function __construct(
        ApplicationContext &$applicationContext,
        $className,
        array &$configuration = null,
        \ReflectionClass &$reflector = null
    ) {
        $this->_applicationContext = $applicationContext;
        $this->_className = $className;
        if (is_array($configuration)) {
            $this->setConfiguration($configuration);
            $this->_reflector = $reflector;
            if (array_key_exists(Bean::AUTO_LOAD, $configuration)
                && $configuration[Bean::AUTO_LOAD] === true
            ) {
                // fire object instance action
                $this->getInstance();
            }
        }
    }

    /**
     * Get bean instance status
     *
     * @return bool
     */
    public function hasInstanced()
    {
        return !is_null($this->_instance);
    }

    public function setConfiguration(&$configuration)
    {
        if ($this->_hasConfiguration) {
            throw new \Exception('Has configuration already: ' . print_r($configuration, true));
        }

        // Set Class Name
        if (!array_key_exists(Bean::CLASS_NAME, $configuration)
            || !is_string($configuration[Bean::CLASS_NAME])
        ) {
            throw new \Exception('Bean Configuration Error: Class name is required.');
        }

        // Set init-method
        if (array_key_exists(Bean::INIT_METHOD, $configuration) && is_array($configuration[Bean::INIT_METHOD])) {
            // Normalize init-method format
            foreach ($configuration[Bean::INIT_METHOD] as $methodName => &$value) {
                if (is_string($value)) {
                    $this->_initMethods[$value] = null;
                } else {
                    $this->_initMethods[$methodName] = $value;
                }
            }
        }

        $this->_hasConfiguration = true;

        $this->_rawConfiguration = $configuration;

        // Auto instance bean and injection
        if (count($this->_dependencyInjectionBeans) > 0) {
            $instance = $this->getInstance();
            foreach ($this->_dependencyInjectionBeans as &$dependencyInjectionBean) {
                $property = $dependencyInjectionBean[self::$_KEY_R_PROPERTY];
                $property->setAccessible(true);
                $property->setValue($dependencyInjectionBean[self::$_KEY_R_BEAN], $instance);
            }
        }
    }

    /**
     * Get class instance
     *
     * @return object
     */
    public function &getInstance()
    {
        // TODO: non-singleton @Scope(singleton/prototype) anotation
        if (is_null($this->_instance) && $this->_hasConfiguration) {
            $this->_applicationContext->log("Instance Class: $this->_className");
            if (isset($this->_rawConfiguration[Bean::INCLUDE_PATH])) {
                include_once($this->_rawConfiguration[Bean::INCLUDE_PATH]);
            }
            $object = new $this->_className;
            $this->setInstance($object);
        }
        return $this->_instance;
    }

    /**
     * Inject self instance into other object
     *
     * @param Object $bean
     * @param \ReflectionProperty $property
     */
    public function inject(&$bean, \ReflectionProperty &$property)
    {
        $instance = $this->getInstance();
        if (!is_null($instance)) {
            $property->setAccessible(true);
            $property->setValue($bean, $this->_instance);
        } else {
            $this->_applicationContext->log(
                'Record inject stage: ' . get_class($bean) . '->' . $property->getName() . ' :: ' . $this->_className
            );
            array_push($this->_dependencyInjectionBeans, array(
                self::$_KEY_R_BEAN => $bean,
                self::$_KEY_R_PROPERTY => $property
            ));
        }
    }

    /**
     * Set instance object into this factory sample
     *
     * @throws \Exception
     * @param object $ref
     * @return void
     */
    public function setInstance(&$ref)
    {
        $this->_instance = $ref;

        if (is_null($this->_reflector)) {
            $this->_reflector = new \ReflectionClass($this->_instance);
        }

        // fire interface has be instance
        $interfaces = array_keys($this->_reflector->getInterfaces());
        foreach ($interfaces as &$interfaceName) {
            $this->_applicationContext->makeInterfaceRefs($interfaceName, $this);
        }

        $this->_applicationContext->injection($this->_instance, $this->_reflector);

        // set properties
        if (array_key_exists(Bean::PROPERTY, $this->_rawConfiguration)) {
            foreach ($this->_rawConfiguration[Bean::PROPERTY] as $name => &$value) {
                // TODO: implement other type
                if (!is_string($value)) {
                    throw new \Exception('Property value not a string.');
                }
                // property member inject
                if ($this->_reflector->hasProperty($name)) {
                    $property = $this->_reflector->getProperty($name);
                    $property->setAccessible(true);
                    $property->setValue($this->_instance, $value);
                } elseif ($this->_reflector->hasProperty('_' . $name)) {
                    $property = $this->_reflector->getProperty('_' . $name);
                    $property->setAccessible(true);
                    $property->setValue($this->_instance, $value);
                }
            }
        }

        // invoke init-method with params
        foreach ($this->_initMethods as $methodName => &$params) {
            if (method_exists($this->_instance, $methodName)) {
                if (is_array($params)) {
                    // has parameter
                    call_user_func_array(array($this->_instance, $methodName), $params);
                } else {
                    // without parameter
                    call_user_func(array($this->_instance, $methodName));
                }
            } else {
                throw new \Exception('Initialize method not found: ' . $methodName);
            }
        }
    }
}
