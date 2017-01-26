<?php

namespace StdPlugin;

class MyClass {

    public $vars;

    public function init($args)
    {
        $this->vars[__METHOD__] = $args;
    }

    public function inits($a, $b)
    {
        $this->vars[__METHOD__]['a'] = $a;
        $this->vars[__METHOD__]['b'] = $b;
    }
} 