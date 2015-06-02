<?php

namespace point\core;

/**
 * Class Context
 *
 * @author sj
 */
class Context
{

    /**
     * Beans repository (key is class name)
     *
     * @var array
     */
    private $_beanMapByClassName = array();

    /**
     * Beans repository (key is id)
     *
     * @var array
     */
    private $_beanMapById = array();

    /**
     * Singleton Context point
     *
     * @var Context
     */
    private static $_self = null;

    /**
     * Context configuration
     *
     * @var array
     */
    private $_config = array();

    /**
     * Get singleton instance object of Context class
     *
     * @return Context
     */
    public static function getInstance()
    {
        if (Context::$_self == null) {
            Context::$_self = new Context();
        }
        return Context::$_self;
    }

    /**
     * Construct
     */

    /**
     * Construct
     *
     * @param array $initConfig init application context configuration
     */
    public function __construct($initConfig = null)
    {
        // load family class
        require_once dirname(__FILE__) . '/Bean.php';
        require_once dirname(__FILE__) . '/BeanFactory.php';

        // extend config (init default configuration)
        $this->_config = array(
            'injectPropertyType' =>
                \ReflectionProperty::IS_PRIVATE |
                \ReflectionProperty::IS_PUBLIC |
                \ReflectionProperty::IS_PROTECTED,
            'debug' => false
        );
        if (is_array($initConfig)) {
            foreach ($initConfig as $name => &$value) {
                $this->_config[$name] = $value;
            }
        }

        if (is_null(Context::$_self)) {
            Context::$_self = $this;
        }
        $this->setBean($this);
    }

    /**
     * Set debug enabled
     *
     * @param boolean $enable
     */
    public function setDebug($enable)
    {
        $this->_config['debug'] = $enable;
    }

    /**
     * Log message
     *
     * @param string $message
     * @see Context->setDebug()
     */
    public function log($message)
    {
        // TODO: fire event
        if ($this->_config['debug']) {
            echo $message . "\n";
        }
    }

    /**
     * Add bean configuration to application container
     *
     * @param array $configurations
     */
    public function addConfiguration($configurations)
    {
        foreach ($configurations as &$configuration) {
            $className = $this->normalizeClassName($configuration[Bean::CLASS_NAME]);
            if (array_key_exists($className, $this->_beanMapByClassName)) {
                // create and update configuration
                $this->_beanMapByClassName[$className]->setConfiguration($configuration);
            } else {
                // make a new bean repository
                $this->_instanceBeanFactory($className, $configuration);
            }
        }
    }

    /**
     * Fix class name and force to add prefix '\'
     *
     * @param string $className Class name
     * @return string
     */
    public function normalizeClassName($className)
    {
        // Normalize class namespace on first word
        if (strpos($className, '\\') !== false && substr($className, 0, 1) !== '\\') {
            return '\\' . $className;
        }
        return $className;
    }

    /**
     * Set bean into container
     *
     * @param object $bean
     */
    public function setBean($bean)
    {
        $className = $this->normalizeClassName(get_class($bean));
        if (!array_key_exists($className, $this->_beanMapByClassName)) {
            // make a new bean repository
            $configuration = array(
                Bean::CLASS_NAME => $className
            );
            $this->_instanceBeanFactory($className, $configuration);
        }
        $this->_beanMapByClassName[$className]->setInstance($bean);
    }

    /**
     * Get bean by class name
     *
     * @param string $className
     * @return object
     */
    public function &getBeanByClassName($className)
    {
        $className = $this->normalizeClassName($className);
        if (!array_key_exists($className, $this->_beanMapByClassName)) {
            // make a new bean repository
            $this->_instanceBeanFactory($className);
        }
        return $this->_beanMapByClassName[$className]->getInstance();
    }

    /**
     * Get ReflectionClass by class name
     *
     * @param string $className
     * @return \ReflectionClass
     */
    public function &getReflectionClass($className)
    {
        $className = $this->normalizeClassName($className);
        return $this->_beanMapByClassName[$className]->getReflectionClass();
    }

    /**
     * Inject resource into instance
     *
     * @param Object           $bean
     * @param \ReflectionClass $reflector
     * @return \ReflectionClass
     */
    public function injection(&$bean, \ReflectionClass &$reflector = null)
    {
        if (is_object($bean)) {
            if (is_null($reflector)) {
                $reflector = new \ReflectionClass($bean);
            }

            // Annotation Injection
            $properties = $reflector->getProperties($this->_config['injectPropertyType']);
            // Parse doc
            foreach ($properties as &$property) {
                $doc = $property->getDocComment();

                // by type to injection object instance when find @Autowired and @var annotation.
                if (preg_match('/@Autowired/', $doc) > 0
                    && preg_match('/@var ([A-Za-z0-9_\\\\]+)/', $doc, $matches) > 0
                ) {
                    $className = $this->normalizeClassName($matches[1]);
                    // use class name inject
                    if (!array_key_exists($className, $this->_beanMapByClassName)) {
                        // make a new bean set to repository
                        $this->_instanceBeanFactory($className, null, $reflector);
                    }

                    $this->_beanMapByClassName[$className]->inject($bean, $property);
                }

                //TODO @Qualifier spec bean inject
            }
            return $reflector;
        }
        return null;
    }

    /**
     * Share object instance to the interface
     *
     * @param string       $interfaceName
     * @param BeanFactory  $beanFactory
     */
    public function makeInterfaceRefs($interfaceName, BeanFactory &$beanFactory)
    {
        $interfaceName = $this->normalizeClassName($interfaceName);
        // create interface map
        if (!$this->hasRegister($interfaceName)) {
            $this->log('Make Interface Refs: ' . $interfaceName);
            $this->_beanMapByClassName[$interfaceName] = $beanFactory;
        } else {
            // After inject, bean instance already must to inject on here
            $this->_beanMapByClassName[$interfaceName]->setInstance($beanFactory->getInstance());
        }
    }

    /**
     * Check class pr interface name was registered
     *
     * @param string $className
     * @return bool
     */
    public function hasRegister($className)
    {
        return array_key_exists($className, $this->_beanMapByClassName);
    }

    /**
     * Create bean factory
     *
     * @param string $className
     * @param array $configuration
     * @param \ReflectionClass $reflector
     */
    private function _instanceBeanFactory(
        $className,
        $configuration = null,
        \ReflectionClass &$reflector = null
    ) {
        $this->log("Instance BeanFactory: $className");
        $beanFactory = new BeanFactory($this, $className, $configuration, $reflector);
        $this->_beanMapByClassName[$className] = & $beanFactory;
        if (!is_null($configuration)) {
            if (array_key_exists(Bean::ID, $configuration)) {
                $this->_beanMapById[$configuration[Bean::ID]] = & $beanFactory;
            }
        }
    }
}
