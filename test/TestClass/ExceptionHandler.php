<?php

namespace point\core\test;

class ExceptionHandler
{
    public function exceptionHandler(\Exception &$exception)
    {
        echo '*********';
        return true;
    }
}
