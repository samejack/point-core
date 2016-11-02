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

    public function testGetPluginsConfig()
    {
        $this->_runtime->stop('PluginC');

        $pluginConfig = $this->_runtime->getPluginConfig('PluginC');
        $this->assertEquals($pluginConfig['SymbolicName'], 'PluginC');

        $pluginConfigs = $this->_runtime->getPluginsConfig();
        $this->assertArrayHasKey('PluginC', $pluginConfigs);
    }

    public function testGetResourcePath()
    {
        $resourcePath = $this->_runtime->getResourcePath('file.mimetype');
        $this->assertNull($resourcePath);

        $resourcePath = $this->_runtime->getResourcePath('file.mimetype', 'PluginC');
        $this->assertEquals($resourcePath, __DIR__ . '/TestPlugins/PluginC/file.mimetype');
    }


    public function testStartPluginException()
    {
        $catchException = null;
        try {
            $this->_runtime->start('not.exist.plugin');
        } catch (\Exception $exception) {
            $catchException = $exception;
        }
        $str = 'Plugin not found. id=not.exist.plugin';
        $this->assertContains($str, $catchException->getMessage());
    }

    public function testStopPlugin()
    {
        $catchException = null;
        $this->_runtime->stop('PluginE.Child');
    }

    public function testStopPluginException()
    {
        $catchException = null;
        try {
            $this->_runtime->stop('not.exist.plugin');
        } catch (\Exception $exception) {
            $catchException = $exception;
        }
        $str = 'Plugin not found. id=not.exist.plugin';
        $this->assertContains($str, $catchException->getMessage());
    }

    public function testClose()
    {
        $this->_runtime->close();
    }
}
