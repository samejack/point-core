<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Bootstrap;

class PlatformClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadClass()
    {
        $bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => false
        ));

        $framework = $bootstrap->getFramework();
        $runtime = $framework->getRuntime();
        $runtime->start('PluginC');
    }

    public function testLoadUnDependsPluginClass()
    {
        $bootstrap = new Bootstrap(array(
            'pluginPath' => __DIR__ . '/TestPlugins',
            'displayError' => false,
            'displayErrorLevel' => E_ALL,
            'defaultTimeZone' => 'UTC',
            'debug' => false
        ));

        $framework = $bootstrap->getFramework();
        $runtime = $framework->getRuntime();

        $catchException = null;
        try {
            $runtime->start('PluginB');
        } catch (\Exception $exception) {
            $catchException = $exception;
        }
        $str = 'Try to load a class in non-dependency plugin \'PluginC\'. (PluginC\PluginCClass) current: PluginB';
        $this->assertContains($str, $catchException->getMessage());

        $activity = $framework->getContext()->getBeanByClassName('\PluginB\Activity');

        $runtime->setCurrentPluginId('PluginB');
        $activity->loadUnExistPluginClass();
        $runtime->restoreCurrentPluginId();
    }

}
