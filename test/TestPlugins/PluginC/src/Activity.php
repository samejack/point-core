<?php

namespace PluginC;

class Activity {

    public function start()
    {
        new \PluginA\CrossClass();
    }

    public function getDependObject()
    {
        new \NonPluginC\PlatformAutoLoadTest();
    }
} 