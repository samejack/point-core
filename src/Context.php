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
     * Reset context singleton instance
     */
    public static function resetInstance()
    {
        Context::$_self = null;
    }

    /**
     * Construct
     *
     * @param array $initConfig init application context configuration
     */
    public function __construct($initConfig = null)
    {
        // load family class
        require_once __DIR__ . '/Bean.php';
        require_once __DIR__ . '/BeanFactory.php';

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
            $className = debug_backtrace()[1]['class'];
            $space = ' ';
            if (strlen($className) < 24) {
                $space = str_repeat(' ', 24 - strlen($className));
            }
            echo '[' . debug_backtrace()[1]['class'] . ']' . $space . $message . "\n";
        }
    }

    /**
     * Add bean configuration to application container
     *
     * @param array $configurations
     */
    public function addConfiguration($configurations, $replace = false)
    {
        foreach ($configurations as &$configuration) {
            $className = $this->normalizeClassName($configuration[Bean::CLASS_NAME]);

            // class name setting
            if (!array_key_exists($className, $this->_beanMapByClassName)) {
                // make a new bean repository
                $this->_instanceBeanFactoryByClassName($className, $configuration);
            } else if (!$this->_beanMapByClassName[$className]->hasConfiguration() || $replace) {
                // create and update configuration
                $this->_beanMapByClassName[$className]->setConfiguration($configuration, $replace);
            }

            // bean id setting
            if (isset($configuration[Bean::ID])) {
                if (!isset($this->_beanMapById[$configuration[Bean::ID]])) {
                    // make a new bean with different bean ID
                    $this->_instanceBeanFactoryById($configuration[Bean::ID], $configuration);
                } else if (!$this->_beanMapById[$configuration[Bean::ID]]->hasConfiguration() || $replace) {
                    // create and update configuration
                    $this->_beanMapById[$configuration[Bean::ID]]->setConfiguration($configuration, $replace);
                }
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
            $this->_instanceBeanFactoryByClassName($className, $configuration);
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
            $this->_instanceBeanFactoryByClassName($className);
        }
        return $this->_beanMapByClassName[$className]->getInstance();
    }

    /**
     * Get bean by id
     *
     * @param string $id
     * @return object
     */
    public function &getBeanById($id)
    {
        if (!array_key_exists($id, $this->_beanMapById)) {
            // make a new bean repository
            return null;
        }
        return $this->_beanMapById[$id]->getInstance();
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

                // @Autoload an be set configuration automatic
                if (preg_match('/@Autoload/', $doc) > 0
                    && preg_match('/@var ([A-Za-z0-9_\\\\]+)/', $doc, $matches) > 0
                ) {
                    $className = $this->normalizeClassName($matches[1]);
                    $config = array(
                        array(
                            Bean::CLASS_NAME => $className
                        )
                    );
                    $this->addConfiguration($config);
                }

                // by type to injection object instance when find @Autowired and @var annotation.
                if (preg_match('/@Autowired/', $doc) > 0
                    && preg_match('/@var ([A-Za-z0-9_\\\\]+)/', $doc, $matches) > 0
                ) {
                    $className = $this->normalizeClassName($matches[1]);
                    // use class name inject
                    if (!array_key_exists($className, $this->_beanMapByClassName)) {
                        // make a new bean set to repository
                        $this->_instanceBeanFactoryByClassName($className, null, $reflector);
                    }

                    $this->_beanMapByClassName[$className]->inject($bean, $property);
                } else if (preg_match('/@Qualifier\([\'"]?([^\'"]+)[\'"]?\)/', $doc, $matches) > 0) {
                    // Qualifier
                    $beanId = $matches[1];
                    if (!array_key_exists($beanId, $this->_beanMapById)) {
                        $this->_instanceBeanFactoryById($beanId, null, $reflector);
                    }
                    $this->_beanMapById[$beanId]->inject($bean, $property);
                }
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
     * @param object       $intanceRef
     */
    public function makeInterfaceRefs($interfaceName, BeanFactory &$beanFactory, &$intanceRef)
    {
        $interfaceName = $this->normalizeClassName($interfaceName);
        // create interface map
        if (!$this->hasRegister($interfaceName)) {
            $this->log('Make Interface Refs: ' . $interfaceName);
            $this->_beanMapByClassName[$interfaceName] = $beanFactory;
        } else if (!$this->_beanMapByClassName[$interfaceName]->hasInstanced()) {
            // After inject, bean instance already must to inject on here
            $this->log('Inject Interface: ' . $interfaceName);
            $this->_beanMapByClassName[$interfaceName]->setInstance($intanceRef);
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
     * Get bean factory by bean id
     *
     * @param string           $id
     * @throws \Exception
     */
    public function getBeanFactoryById($id)
    {
        if (isset($this->_beanMapById[$id])) {
            return $this->_beanMapById[$id];
        }
        return null;
    }

    /**
     * Create bean factory and set by class name
     *
     * @param string           $className
     * @param array            $configuration
     * @param \ReflectionClass $reflector
     * @throws \Exception
     */
    private function _instanceBeanFactoryByClassName(
        $className,
        $configuration = null,
        \ReflectionClass &$reflector = null
    ) {
        $this->log('Create BeanFactory by ClassName = ' . $className);
        $beanFactory = new BeanFactory($this, $configuration, $reflector);

        // set to class name hash table
        $this->log('Set bean map by ClassName = ' . $className);
        $this->_beanMapByClassName[$className] = &$beanFactory;

        // set to id hash table
        if (!is_null($configuration) && array_key_exists(Bean::ID, $configuration)) {
            if (isset($this->_beanMapById[$configuration[Bean::ID]])) {
                throw new \Exception('Bean id is already existed: ' . $configuration[Bean::ID]);
            }
            $this->log('Set bean map by ID = ' . $configuration[Bean::ID]);
            $this->_beanMapById[$configuration[Bean::ID]] = &$beanFactory;
        }
    }

    /**
     * Create bean factory and set by bean id
     *
     * @param string           $id
     * @param array            $configuration
     * @param \ReflectionClass $reflector
     * @throws \Exception
     */
    private function _instanceBeanFactoryById(
        $id,
        $configuration = null,
        \ReflectionClass &$reflector = null
    ) {
        // set to id hash table
        if (isset($this->_beanMapById[$id])) {
            throw new \Exception('Bean id is already existed: ' . $configuration[Bean::ID]);
        }

        $this->log('Create BeanFactory by  = ' . $id);
        $beanFactory = new BeanFactory($this, $configuration, $reflector);
        $this->log('Set bean map by ID = ' . $id);
        $this->_beanMapById[$id] = &$beanFactory;
    }
}
