<?php

namespace PluginB;

class Activity {

    public function start()
    {
        new \PluginC\PluginCClass();
    }

    public function loadUnExistPluginClass()
    {
        //new \PHPUnit_Framework_Exception('!!');
    }

} 