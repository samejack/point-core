<?php

namespace point\core\test;

class Property
{
    protected $_protectedVar = null;

    private $_autoUnderLine = null;

    private $_priVar = null;

    public $pubVar = null;

    public function getVars()
    {
        return array(
            '_priVar' => $this->_priVar,
            'pubVar' => $this->pubVar,
            '_autoUnderLine' => $this->_autoUnderLine,
            '_protectedVar' => $this->_protectedVar
        );
    }
}
