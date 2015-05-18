<?php

namespace point\core\test;

class ConstructorArgs
{
    public $arg1;
    public $arg2;

    public function __construct($arg1, $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}
