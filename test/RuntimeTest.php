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

    private $_bootstrap;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $this->_bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => false
        ));
        $this->_runtime = $this->_bootstrap->getFramework()->getRuntime();
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

    public function testPluginExtension()
    {
        $this->_runtime->install(__DIR__ . '/TestPlugins/ExtensionChild1');
        $this->_runtime->resolve('extension.child.1');
        $this->_runtime->install(__DIR__ . '/TestPlugins/ExtensionChild2');
        $this->_runtime->resolve('extension.child.2');

        $extension = $this->_runtime->getExtension('TestPointName', 'extension.parent');
        $this->assertArrayHasKey('extension.child.1', $extension);
        $this->assertArrayHasKey('extension.child.2', $extension);
        $this->assertArrayHasKey('Text', $extension['extension.child.1'][0]);
        $this->assertEquals('Child 1', $extension['extension.child.1'][0]['Text']);
        $this->assertEquals('Child 2', $extension['extension.child.2'][0]['Text']);
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

    public function testPluginInstall()
    {
        $pluginId = $this->_runtime->install(__DIR__ . '/TestErrorPlugins2/PluginConfigErr');
        $this->assertEquals($pluginId, 'PluginConfigErr');
    }

    public function testPluginResolve()
    {
        $pluginId = $this->_runtime->install(__DIR__ . '/TestErrorPlugins2/PluginConfigErr');
        $result = $this->_runtime->resolve($pluginId);
        $this->assertTrue($result);

        try {
            $this->_runtime->resolve('GG');
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Plugin can\'t resolved. Plugin id not found. (id=GG)');
        }
    }

    public function testPluginStart()
    {
        $pluginId = $this->_runtime->install(__DIR__ . '/TestPlugins/StdPlugin');
        $this->_runtime->resolve($pluginId);
        $this->_runtime->start($pluginId);
        $this->_runtime->stop($pluginId);
    }

    public function testInitMethod()
    {
        $this->_runtime->start('StdPlugin');
        $this->_bootstrap->getFramework()->getRuntime()->setCurrentPluginId('StdPlugin');
        $myClass = $this->_bootstrap->getFramework()->getContext()->getBeanByClassName('\StdPlugin\MyClass');

        $this->assertArrayHasKey('StdPlugin\MyClass::init', $myClass->vars);
        $this->assertEquals('string!', $myClass->vars['StdPlugin\MyClass::init']);

        $this->assertArrayHasKey('StdPlugin\MyClass::inits', $myClass->vars);
        $this->assertEquals([1, 2, 3], $myClass->vars['StdPlugin\MyClass::inits']['a']);
        $this->assertEquals('b!', $myClass->vars['StdPlugin\MyClass::inits']['b']);
    }

    public function testClose()
    {
        $this->_runtime->start('StdPlugin');
        $this->_runtime->close();
    }
}
