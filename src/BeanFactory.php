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

    private $_instance = null;
    private $_initMethods = array();
    private $_dependencyInjectionBeans = array();
    private $_injectedBeansHistory = array();
    private $_hasConfiguration = false;

    private $_included = false;

    /**
     * @var ReflectionClass
     */
    private $_reflector = null;

    /**
     * @var Array
     */
    private $_rawConfiguration;

    /**
     * @var Context
     */
    private $_context;

    /**
     * Construct
     *
     * @param Context            $_context           Point Application Context
     * @param array              $configuration      Bean Configuration
     * @param \ReflectionClass   $reflector
     */
    public function __construct(
        Context &$_context,
        array $configuration = null,
        \ReflectionClass &$reflector = null
    ) {
        $this->_context = $_context;
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

    public function getConfiguration()
    {
        return $this->_rawConfiguration;
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

    /**
     * Get bean configuration status
     *
     * @return bool
     */
    public function hasConfiguration()
    {
        return $this->_hasConfiguration;
    }

    /**
     * Get ReflectionClass of bean
     *
     * @return \ReflectionClass
     */
    public function &getReflectionClass()
    {
        return $this->_reflector;
    }

    public function setConfiguration(&$configuration, $replace = false)
    {
        if ($replace === false && $this->_hasConfiguration) {
            throw new \Exception('Has configuration already: ' . $configuration[Bean::CLASS_NAME]);
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
     * Refence @Scope(singleton/prototype) anotation
     *
     * @return object
     */
    public function &getInstance()
    {
        // singleton scope
        if (is_null($this->_instance) && $this->hasConfiguration()) {
            if (!$this->_included && isset($this->_rawConfiguration[Bean::INCLUDE_PATH])) {
                $this->_included = true;
                include_once($this->_rawConfiguration[Bean::INCLUDE_PATH]);
            }

            $object = $this->_make();
            $this->setInstance($object);
            $this->_context->log('Singleton scope, instance: ' . get_class($object));
            return $this->_instance;
        }

        // prototype scope
        if (isset($this->_rawConfiguration[Bean::SCOPE])
            && $this->_rawConfiguration[Bean::SCOPE] === Bean::SCOPE_PROTOTYPE
        ) {
            $object = $this->_make();
            $this->_context->log('Prototype scope, instance: ' . get_class($object));
            $this->setInstance($object);
        }

        return $this->_instance;
    }

    /**
     * Reinstance class and inject again
     *
     * @return bool|null
     * @throws \Exception
     */
    public function renew()
    {
        if ($this->hasConfiguration()) {
            if (isset($this->_rawConfiguration[Bean::INCLUDE_PATH])) {
                include_once($this->_rawConfiguration[Bean::INCLUDE_PATH]);
            }

            $instance = $this->_make();
            $this->setInstance($instance);
            $this->_context->log('Renew instance: ' . get_class($instance));

            // inject again
            foreach ($this->_injectedBeansHistory as &$beanInfo) {
                $bean = $beanInfo[0];
                $property = $beanInfo[1];
                if ($property->isPrivate() || $property->isProtected()) {
                    $property->setAccessible(true);
                    $property->setValue($bean, $instance);
                    $property->setAccessible(false);
                } else {
                    $property->setValue($bean, $this->_instance);
                }
            }

            return $this->_instance;
        }
        return false;
    }

    /**
     * Instance class
     *
     * @return object
     * @throws \Exception
     */
    private function _make()
    {
        $className = $this->_rawConfiguration[Bean::CLASS_NAME];
        $this->_context->log('Instance Class: ' . $className);
        if (is_null($this->_reflector)) {
            $this->_reflector = new \ReflectionClass($className);
        }
        if (isset($this->_rawConfiguration[Bean::CONSTRUCTOR_ARG])) {
            if (!is_array($this->_rawConfiguration[Bean::CONSTRUCTOR_ARG])) {
                throw new \Exception('CONSTRUCTOR_ARG not a array.');
            }
            return $this->_reflector->newInstanceArgs($this->_rawConfiguration[Bean::CONSTRUCTOR_ARG]);
        } else {
            return $this->_reflector->newInstance();
        }
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
            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
                $property->setValue($bean, $instance);
                $property->setAccessible(false);
            } else {
                $property->setValue($bean, $this->_instance);
            }
            $this->_injectedBeansHistory[] = [$bean, $property];
        } else {
            array_push($this->_dependencyInjectionBeans, array(
                self::$_KEY_R_BEAN => $bean,
                self::$_KEY_R_PROPERTY => $property
            ));
            $this->_context->log(
                'Record inject stage(' . count($this->_dependencyInjectionBeans) . '): ' .
                get_class($bean) . '->' . $property->getName()
            );
        }
    }

    /**
     * Set instance object into this factory sample
     *
     * @throws \Exception
     * @param object   $ref           Instance object
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
            // skip php native interface
            if (strpos($interfaceName, '\\') !== false) {
                $this->_context->makeInterfaceRefs($interfaceName, $this, $this->_instance);
            }
        }

        $this->_context->injection($this->_instance, $this->_reflector);

        // set properties
        if (is_array($this->_rawConfiguration)
            && array_key_exists(Bean::PROPERTY, $this->_rawConfiguration)
        ) {
            foreach ($this->_rawConfiguration[Bean::PROPERTY] as $name => &$value) {
                // TODO: implement other type
                if (!is_string($value)) {
                    throw new \Exception('Property value not a string.');
                }
                // property member inject (include prefix _)
                $property = null;
                if ($this->_reflector->hasProperty($name)) {
                    $property = $this->_reflector->getProperty($name);
                } else if ($this->_reflector->hasProperty('_' . $name)) {
                    $property = $this->_reflector->getProperty('_' . $name);
                }
                if (!is_null($property)) {
                    if ($property->isPrivate() || $property->isProtected()) {
                        $property->setAccessible(true);
                        $property->setValue($this->_instance, $value);
                        $property->setAccessible(false);
                    } else {
                        $property->setValue($this->_instance, $value);
                    }
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
                throw new \Exception('Initialize invoke method not found: ' . $methodName);
            }
        }
    }
}
