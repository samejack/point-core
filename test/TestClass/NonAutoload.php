<?php

namespace point\core\test;

class NonAutoload
{
    public static $INSTANCE = null;

    public function __construct()
    {
        self::$INSTANCE = $this;
    }
}
