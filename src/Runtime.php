<?php

namespace point\core;

/**
 * Runtime class
 *
 * @author sj
 */
class Runtime
{

    const UNINSTALLED = 'UNINSTALLED';
    const INSTALLED = 'INSTALLED';
    const RESOLVED = 'RESOLVED';
    const STOPPING = 'STOPPING';
    const CLOSED = 'CLOSED';
    const ACTIVE = 'ACTIVE';

    private $_currentPluginId = null;
    private $_currentPluginIdHistory = array();
    private $_plugins = array();
    private $_extensions = array();
    private $_startPluginList = array();

    /**
     * @Autowired
     * @var \point\core\Context
     */
    private $_context;

    /**
     * Get extension points
     *
     * @param {string}   $name      extension point name
     * @param {string}   $pluginId  plugin id [optional]
     * @return array
     */
    public function getExtension($name, $pluginId = null)
    {
        if (is_null($pluginId)) {
            $pluginId = $this->getCurrentPluginId();
        }
        if (isset($this->_extensions[$name . '@' . $pluginId])) {
            return $this->_extensions[$name . '@' . $pluginId];
        }
        return array();
    }

    /**
     * Get plugin by id
     *
     * @param {string} $pluginId plugin id
     * @return array
     */
    public function &getPluginConfig($pluginId)
    {
        if (array_key_exists($pluginId, $this->_plugins)) {
            return $this->_plugins[$pluginId];
        }
        return null;
    }

    /**
     * Get all plugin's configuration
     *
     * @return array
     */
    public function &getPluginsConfig()
    {
        return $this->_plugins;
    }

    /**
     * Install plugin and registe extension
     *
     * @param String $pluginId plugin id
     * @return boolean success or fail
     */
    public function install($pluginPath)
    {

        $filename = $pluginPath . '/plugin.php';
        if (!is_file($filename)) {
            die(sprintf('Plugin configuration file not found. (%s)', $filename));
        } else {
            // initial var
            $point = array();
            $extension = array();
            // load plugin.php file
            require_once $filename;
            unset($filename);
        }

        if (!array_key_exists('SymbolicName', $point)) {
            die('Plugin SymbolicName not defined.');
        } else {
            $pluginId = $point['SymbolicName'];
            $this->_plugins[$pluginId]['Status'] = Runtime::UNINSTALLED;
        }

        // check plugin is enable
        if (!array_key_exists('Enabled', $point) || !$point['Enabled']) {
            return false;
        }

        // save config
        $this->_plugins[$pluginId] = &$point;

        // fix plugin name
        if (!array_key_exists('Name', $this->_plugins[$pluginId])) {
            $this->_plugins[$pluginId]['Name'] = 'NoName';
        }

        // fix plugin version
        if (!array_key_exists('Version', $this->_plugins[$pluginId])) {
            $this->_plugins[$pluginId]['Version'] = '0.0';
        }

        $this->_plugins[$pluginId]['Path'] = $pluginPath;

        //extension points registed
        if (is_array($extension)) {
            foreach (array_keys($extension) as $extPlugin) {
                if (is_array($extension[$extPlugin])) {
                    foreach (array_keys($extension[$extPlugin]) as $extensionName) {
                        if (is_array($extension[$extPlugin][$extensionName])) {
                            foreach ($extension[$extPlugin][$extensionName] as $key => $value) {
                                $this->_extensions[$extensionName . '@' . $extPlugin][$pluginId][$key] = $value;
                            }
                        } elseif (!isset($this->_extensions)) {
                            die('Can not registe Plugin Extension Point : Plugin "' . $extPlugin . '" not found!');
                        } else {
                            array_push(
                                $this->_extensions[$extensionName . '@' . $extPlugin],
                                $extension[$extPlugin][$extensionName]
                            );
                        }
                    }
                }
            }
        }

        // update status
        $this->_plugins[$pluginId]['Status'] = Runtime::INSTALLED;

        return true;
    }

    /**
     * Resolve plugin
     *
     * @param String $pluginId plugin id
     * @return boolean success or fail
     */
    public function resolve($pluginId)
    {

        // load plugin configuration
        if (!array_key_exists($pluginId, $this->_plugins)) {
            die(sprintf('Plugin can\'t resolved. Plugin id not found. (id=%s)', $pluginId));
        }

        // TODO: plugin fragment
        if (array_key_exists('Type', $this->_plugins[$pluginId])
            && strtoupper($this->_plugins[$pluginId]['Type']) === 'FRAGMENT'
        ) {
            //TODO implement
            return true;
        }

        // load beans
        if (array_key_exists('Beans', $this->_plugins[$pluginId])) {
            $this->_context->addConfiguration($this->_plugins[$pluginId]['Beans']);
        }

        $this->_plugins[$pluginId]['Status'] = Runtime::RESOLVED;

        return true;
    }


    /**
     * Start plugin
     *
     * @param String plugin id
     * @return void
     */
    public function start($pluginId)
    {

        if (!array_key_exists($pluginId, $this->_plugins)) {
            die(sprintf('Plugin not found. id=%s', $pluginId));
        }

        // resolve plugin self
        if ($this->_plugins[$pluginId]['Status'] === Runtime::INSTALLED) {
            $this->resolve($pluginId);
        }

        // start plugin
        if ($this->_plugins[$pluginId]['Status'] === Runtime::RESOLVED) {
            //start plugin's depends
            if (array_key_exists('Depends', $this->_plugins[$pluginId])
                && is_array($this->_plugins[$pluginId]['Depends'])) {
                foreach ($this->_plugins[$pluginId]['Depends'] as $subPluginId) {
                    if (!array_key_exists($subPluginId, $this->_startPluginList)) {
                        $this->start($subPluginId);
                    }
                }
            }

            $this->_plugins[$pluginId]['Status'] = 'STARTING';

            if (array_key_exists('Activator', $this->_plugins[$pluginId])) {
                $this->setCurrentPluginId($pluginId);

                $classFullName = str_replace('.', '\\', $pluginId) . '\\' . $this->_plugins[$pluginId]['Activator'];

                $configurations = array(
                    array(
                        Bean::CLASS_NAME => $classFullName,
                        Bean::INIT_METHOD => array('start')
                    )
                );
                $this->_context->addConfiguration($configurations);

                $this->_context->getBeanByClassName($classFullName);

                //record start plugin name
                if (!array_key_exists($pluginId, $this->_startPluginList)) {
                    $this->_startPluginList[$pluginId] = $pluginId;
                }

                $this->restoreCurrentPluginId();
            }

            // update status
            $this->_plugins[$pluginId]['Status'] = Runtime::ACTIVE;

        }
    }


    /**
     * Stop plugin
     *
     * @param String plugin id
     */
    public function stop($pluginId)
    {
        if ($this->_plugins[$pluginId]['Status'] === 'ACTIVE') {
            $this->_plugins[$pluginId]['Status'] = Runtime::STOPPING;

            //invoke activator stop
            if (array_key_exists('Activator', $this->_plugins[$pluginId])) {
                $classFullName = str_replace('.', '\\', $pluginId) . '\\' . $this->_plugins[$pluginId]['Activator'];
                $activator = $this->_context->getBeanByClassName($classFullName);
                // stop plugin
                if (!is_null($activator) && method_exists($activator, 'stop')) {
                    $this->setCurrentPluginId($pluginId);
                    $activator->stop();
                    $this->restoreCurrentPluginId();
                }
            }

            $this->_plugins[$pluginId]['Status'] = Runtime::CLOSED;
        }
    }

    public function close()
    {
        foreach (array_keys($this->_startPluginList) as $pluginId) {
            $this->stop($pluginId);
        }
    }

    /**
     * To obtain the absolute URL path by plugin id
     *
     * @param String filename
     * @param String plugin id
     * @return String
     */
    public function getResourceUrl($filename, $pluginId = null)
    {
        if (is_null($pluginId)) {
            $pluginId = $this->getCurrentPluginId();
        }
        return '/public/' . $pluginId . $filename;
    }

    /**
     * To obtain the absolute file system path by plugin id
     *
     * @param string $filename filename
     * @param string $pluginId plugin id [optional]
     * @return string
     */
    public function getResourcePath($filename, $pluginId = null)
    {
        if (is_null($pluginId)) {
            $pluginId = $this->getCurrentPluginId();
        }
        return $this->_plugins[$pluginId]['Path'] . $filename;
    }

    /**
     * Set runtime current plugin id
     *
     * @param String $pluginId plugin id
     * @return void
     */
    public function setCurrentPluginId($pluginId)
    {
        if (!is_null($this->_currentPluginId)) {
            array_push($this->_currentPluginIdHistory, $this->_currentPluginId);
        }
        $this->_currentPluginId = $pluginId;
    }

    /**
     * Get runtime current plugin id
     *
     * @return String plugin id
     */
    public function getCurrentPluginId()
    {
        return $this->_currentPluginId;
    }

    /**
     * Restore current Plugin Id from history
     *
     * @return void
     */
    public function restoreCurrentPluginId()
    {
        $this->_currentPluginId = array_pop($this->_currentPluginIdHistory);
    }
}
