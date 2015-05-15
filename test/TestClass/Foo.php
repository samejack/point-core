<?php

namespace point\core\test;

class Foo
{

    /**
     * @Autowired
     * @var \point\core\test\Bar
     */
    private $_bar;

    /**
     * @Autowired
     * @var \point\core\test\Inject
     */
    private $_inject;

    public function __construct()
    {
    }

    public function getBar()
    {
        return $this->_bar;
    }

    public function getInject()
    {
        return $this->_inject;
    }
}