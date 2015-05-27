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

    /**
     * @Autowired
     * @var \point\core\test\MyInterface
     */
    private $_injectInterface;

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

    public function getInjectInterface()
    {
        return $this->_injectInterface;
    }
}