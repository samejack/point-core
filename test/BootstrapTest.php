<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Bootstrap;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{

    public function testRun()
    {
        $bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
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
    }

}
