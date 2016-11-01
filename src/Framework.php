<?php

namespace point\core;

/**
 * Class Framework
 *
 * @author sj
 */
class Framework
{

    /**
     * @Autowired
     * @var \point\core\Context
     */
    private $_context;

    /**
     * @Autowired
     * @var \point\core\Runtime
     */
    private $_runtime;

    /**
     * execute timer
     * @var long
     */
    private $_startTime = 0;

    /**
     * framework configurations
     * @var array
     */
    private $_config = array();

    /**
     * Framework constructor
     *
     * @param mixed $initConfig Configuration of framework [optional]
     */
    public function __construct(array $initConfig = null)
    {
        // extend config
        $config = array(
            'pluginPath' => realpath(__DIR__ . '/../../..'),
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC'
        );
        if (!is_null($initConfig)) {
            foreach ($initConfig as $key => &$value) {
                $config[$key] = $value;
            }
        }

        date_default_timezone_set($config['defaultTimeZone']);

        //Set start timestamp
        $this->_startTime = microtime(true);

        //Set PHP error report
        if ($config['displayError'] === true) {
            error_reporting($config['displayErrorLevel']);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // fix pluginPath config
        if (is_string($config['pluginPath'])) {
            $config['pluginPath'] = array($config['pluginPath']);
        }
        $this->_config = &$config;
    }

    /**
     * Point Framework Launcher<br>
     * Install adn start plugins
     *
     * @throw \Exception
     */
    public function launcher()
    {

        //load family class
        require_once __DIR__ . '/Runtime.php';
        require_once __DIR__ . '/PlatformClassLoader.php';

        $beans = array(
            array(
                'class' => '\point\core\Runtime',
            ),
            array(
                'class' => '\point\core\PlatformClassLoader',
            ),
            array(
                'class' => '\point\core\EventHandleManager'
            )
        );
        $this->_context->addConfiguration($beans);

        // register plugin and platform class loader
        $eventHandleManager = $this->_context->getBeanByClassName('\point\core\EventHandleManager');
        $eventHandleManager->addClassLoader($this->_context->getBeanByClassName('point\core\PlatformClassLoader'));
        $eventHandleManager->register();

        //install plugin
        foreach ($this->_config['pluginPath'] as $pluginDir) {
            if (!is_dir($pluginDir)) {
                throw new \Exception('Plugin path not found : ' . $pluginDir);
            }
            $pluginsDir = opendir($pluginDir);
            while (($pluginName = readdir($pluginsDir)) !== false) {
                if (substr($pluginName, 0, 1) !== '.') {
                    $this->_runtime->install($pluginDir . '/' . $pluginName);
                }
            }
            closedir($pluginsDir);
        }

        //auto start plugin
        $configs = $this->_runtime->getPluginsConfig();
        foreach ($configs as $pluginId => &$config) {
            if (array_key_exists('AutoStart', $config) && $config['AutoStart'] === true) {
                $this->_runtime->start($pluginId);
            }
        }

        $this->_runtime->close();
    }

    public function getStartTime()
    {
        return $this->_startTime;
    }

    /**
     * Get the time from framework invoke launcher
     *
     * @return Integer
     */
    public function getExecuteTime()
    {
        return microtime(true) - $this->getStartTime();
    }

    /**
     * Get application context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Get runtime
     *
     * @return Runtime
     */
    public function getRuntime()
    {
        return $this->_runtime;
    }


}