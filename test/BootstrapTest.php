<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Bootstrap;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{

    private $_bootstrap;

    public function __construct()
    {
        $this->_bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => false
        ));
    }

    public function testRun()
    {
        $this->assertEquals(get_class($this->_bootstrap), 'point\core\Bootstrap');

        $framework = $this->_bootstrap->getFramework();
        $context = $framework->getContext();
        $runtime = $framework->getRuntime();

        $this->assertEquals(get_class($context), 'point\core\Context');
        $this->assertEquals(get_class($runtime), 'point\core\Runtime');

        $runtime->start('PluginA');

        $pluginAutoLoadTest = new \PluginA\AutoLoadTest();
        $this->assertEquals(get_class($pluginAutoLoadTest), 'PluginA\AutoLoadTest');
    }

    public function testDebugRun()
    {
        $bootstrap = new Bootstrap(array(
            'pluginPath' => array( __DIR__ . '/TestPlugins'),
            'displayError' => true,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => true
        ));
        $this->assertEquals(get_class($bootstrap), 'point\core\Bootstrap');

        $framework = $bootstrap->getFramework();
        $context = $framework->getContext();
        $runtime = $framework->getRuntime();

        $this->assertEquals(get_class($context), 'point\core\Context');
        $this->assertEquals(get_class($runtime), 'point\core\Runtime');

        $runtime->start('PluginA');

        $pluginAutoLoadTest = new \PluginA\AutoLoadTest();
        $this->assertEquals(get_class($pluginAutoLoadTest), 'PluginA\AutoLoadTest');

        $runtime->start('PluginE.Child');
    }

    public function testExtension()
    {
        $runtime = $this->_bootstrap->getFramework()->getRuntime();
        $extension = $runtime->getExtension('Menu', 'PluginD.Parent');

        $this->assertArrayHasKey('PluginE.Child', $extension);
        $this->assertArrayHasKey('Title', $extension['PluginE.Child'][0]);

        $extension = $runtime->getExtension('NotFound', 'PluginD.Parent');
        $this->assertNull($extension);

        $extension = $runtime->getExtension('Menu');
        $this->assertNull($extension);
    }

}
