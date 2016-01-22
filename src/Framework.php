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
     * Framework constructor
     *
     * @param mixed $initConfig Configuration of framework [optional]
     */
    public function __construct($initConfig = null)
    {
        // extend config
        $config = array(
            'PLUGINS_PATH' => __DIR__ . '/../../..',
            'DISPLAY_ERROR' => false,
            'DISPLAY_ERROR_LEVEL' => E_ALL,
            'DEFAULT_TIMEZONE' => 'UTC'
        );
        if (is_array($initConfig)) {
            foreach ($initConfig as $key => &$value) {
                $config[$key] = $value;
            }
        }

        date_default_timezone_set($config['DEFAULT_TIMEZONE']);

        //Set start timestamp
        $this->_startTime = microtime(true);

        //register class loader manager
        require_once dirname(__FILE__) . '/EventHandleManager.php';
        EventHandleManager::register();

        //Set PHP error report
        if ($config['DISPLAY_ERROR']) {
            error_reporting($config['DISPLAY_ERROR_LEVEL']);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        //Constant setup
        define ('PLUGINS_PATH', $config['pluginPath']);

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
        );
        $this->_context->addConfiguration($beans);

        // register plugin and platform class loader
        EventHandleManager::addClassLoader($this->_context->getBeanByClassName('point\core\PlatformClassLoader'));


        //install plugin
        if (!is_dir(PLUGINS_PATH)) {
            throw new \Exception('Plugin path not found : ' . PLUGINS_PATH);
        }
        $pluginsDir = opendir(PLUGINS_PATH);
        while (($pluginName = readdir($pluginsDir)) !== false) {
            if (substr($pluginName, 0, 1) !== '.') {
                $this->_runtime->install(PLUGINS_PATH . '/' . $pluginName);
                $this->_context->log('Install plugin: ' . PLUGINS_PATH . '/' . $pluginName);
            }
        }
        closedir($pluginsDir);

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
}