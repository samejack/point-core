<?php

namespace PluginE\Child;

class Activity {

    /**
     * @var \PluginD.Parent\PluginDClass
     */
    private $_parentObject;

    public function start()
    {
        print_r($this->_parentObject);
    }

} 
