<?php

namespace point\core;

/**
 * Class ApplicationContext
 *
 * @author sj
 */
class ApplicationContext
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
     * Singleton ApplicationContext point
     *
     * @var ApplicationContext
     */
    private static $_self = null;

    /**
     * Debug mode enabled
     *
     * @var boolean
     */
    private $_debug = false;

    /**
     * ApplicationContext configuration
     *
     * @var array
     */
    private $_config = array();

    /**
     * Get singleton instance object of ApplicationContext class
     *
     * @return ApplicationContext
     */
    public static function getInstance()
    {
        if (ApplicationContext::$_self == null) {
            ApplicationContext::$_self = new ApplicationContext();
        }
        return ApplicationContext::$_self;
    }

    /**
     * Construct
     */

    /**
     * Construct
     *
     * @param array $config init application context configuration
     */
    public function __construct($config = null)
    {
        // load family class
        require_once dirname(__FILE__) . '/Bean.php';
        require_once dirname(__FILE__) . '/BeanFactory.php';

        // init default configuration
        $this->_config['injectPropertyType'] =
            \ReflectionProperty::IS_PRIVATE |
            \ReflectionProperty::IS_PUBLIC |
            \ReflectionProperty::IS_PROTECTED;

        // extend config
        if (is_array($config)) {
            foreach ($config as $name => &$value) {
                $this->_config[$name] = $value;
            }
        }

        if (is_null(ApplicationContext::$_self)) {
            ApplicationContext::$_self = $this;
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
        $this->_debug = $enable;
    }

    /**
     * Log message
     *
     * @param string $message
     * @see ApplicationContext->setDebug()
     */
    public function log($message)
    {
        // TODO: fire event
        if ($this->_debug) {
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
     * @param string $interfaceName
     * @param string $beanFactory
     */
    public function makeInterfaceRefs($interfaceName, &$beanFactory)
    {
        $interfaceName = $this->normalizeClassName($interfaceName);
        // create interface map
        if (!array_key_exists($interfaceName, $this->_beanMapByClassName)) {
            $this->log('Make Interface Refs: ' . $interfaceName);
            $this->_beanMapByClassName[$interfaceName] = $beanFactory;
        } else {
            // After inject, bean instance already must to inject on here
            $this->_beanMapByClassName[$interfaceName]->setInstance($beanFactory->getInstance());
        }
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
