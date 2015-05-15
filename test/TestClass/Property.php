<?php

namespace point\core\test;

class Property
{
    private $_priVar = null;

    public $pubVar = null;

    public function getVars()
    {
        return array(
            '_priVar' => $this->_priVar,
            'pubVar' => $this->pubVar
        );
    }
}
