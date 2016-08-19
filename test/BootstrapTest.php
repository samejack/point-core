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
            'defaultTimeZone' => 'UTC'
        ));
        $this->assertEquals(get_class($bootstrap), 'point\core\Bootstrap');
    }

}
