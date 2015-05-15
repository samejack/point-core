<?php

namespace point\core\test;

class Autoload
{
    public static $INSTANCE = null;

    public function __construct()
    {
        self::$INSTANCE = $this;
    }
}
