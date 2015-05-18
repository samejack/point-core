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
     * PHP error report
     * @var bool
     */
    private $_errorReport = true;

    /**
     * execute timer
     * @var long
     */
    private $_startTime = 0;

    public function __construct()
    {
        //Set start timestamp
        $this->_startTime = microtime();

        //registe class loader manager
        require_once dirname(__FILE__) . '/EventHandleManager.php';
        EventHandleManager::register();

        //Set PHP error report style
        if ($this->_errorReport) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        //Constant setup
        define ('PLUGINS_PATH', dirname(dirname(dirname(__FILE__))));

    }

    /**
     * PRSP Launcher<br>
     * Install adn start plugins
     *
     */
    public function launcher()
    {

        //load familly class
        require_once dirname(__FILE__) . '/Runtime.php';
        require_once dirname(__FILE__) . '/PlatformClassLoader.php';

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
            die('Plugin path not found : ' . PLUGINS_PATH);
        }
        $pluginsDir = opendir(PLUGINS_PATH);
        while (($pluginName = readdir($pluginsDir)) !== false) {
            if (substr($pluginName, 0, 1) !== '.') {
                $this->_runtime->install(PLUGINS_PATH . '/' . $pluginName);
            }
        }
        closedir($pluginsDir);

        //auto start plugin
        $configs = $this->_runtime->getPluginsConfig();
        foreach ($configs as $pluginId => &$config) {
            if (array_key_exists('AutoStart', $config) && $config['AutoStart']) {
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
        return microtime() - $this->getStartTime();
    }
}