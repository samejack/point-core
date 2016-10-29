<?php

namespace point\core\test;

include_once __DIR__ . '/../Autoloader.php';

use \point\core\Framework;

class FrameworkTest extends \PHPUnit_Framework_TestCase
{

    public function testGetStartTime()
    {
        $framework = new Framework();
        $this->assertTrue(is_numeric($framework->getStartTime()));
    }

    public function testGetExecuteTime()
    {
        $framework = new Framework();
        $this->assertTrue(is_numeric($framework->getExecuteTime()));
        sleep(1);
        $this->assertTrue($framework->getExecuteTime() > 0);
    }

    public function testDepends()
    {

//        $bootstrap = new Bootstrap(array(
//            'pluginPath' => array( __DIR__ . '/TestPlugins'),
//            'displayError' => true,
//            'displayErrorLevel' => E_ALL,
//            'defaultTimeZone' => 'UTC',
//            'debug' => true
//        ));
//
//        $bootstrap = new Bootstrap(array(
//            'pluginPath' => array( __DIR__ . '/TestPlugins'),
//            'displayError' => true,
//            'displayErrorLevel' => E_ALL,
//            'defaultTimeZone' => 'UTC',
//            'debug' => true
//        ));

//        $framework = $bootstrap->getFramework();
//        $runtime = $framework->getRuntime();

//        $runtime->start('PluginE.Child');

    }

}
