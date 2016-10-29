<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Bootstrap;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \point\core\Runtime
     */
    private $_runtime;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => false
        ));
        $this->_runtime = $bootstrap->getFramework()->getRuntime();
        parent::__construct($name, $data, $dataName);
    }

    public function testAll()
    {
        $this->_runtime->stop('PluginC');

        $pluginConfig = $this->_runtime->getPluginConfig('PluginC');
        $this->assertEquals($pluginConfig['SymbolicName'], 'PluginC');

        $pluginConfigs = $this->_runtime->getPluginsConfig();
        $this->assertArrayHasKey('PluginC', $pluginConfigs);

        $this->_runtime->close();
    }

    public function testGetResourcePath()
    {
        $resourcePath = $this->_runtime->getResourcePath('file.mimetype');
        $this->assertNull($resourcePath);

        $resourcePath = $this->_runtime->getResourcePath('file.mimetype', 'PluginC');
        $this->assertEquals($resourcePath, __DIR__ . '/TestPlugins/PluginC/file.mimetype');
    }

}
