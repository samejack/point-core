<?php

namespace point\core\test;

class Bar
{

    private $_data = null;

    public $autoInit = false;

    public function autoInit()
    {
        $this->autoInit = true;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }
}
