<?php

namespace point\core;

/**
 * Class Bootstrap
 *
 * @author sj
 */
class Bootstrap
{
    /**
     * @var \point\core\Framework
     */
    private $_framework;

    public function __construct($config = null)
    {
        include_once dirname(__FILE__) . '/Context.php';
        include_once dirname(__FILE__) . '/Framework.php';

        $context = new Context($config);
        $context->addConfiguration(
            array(
                array(
                    Bean::CLASS_NAME => '\point\core\Framework',
                    Bean::CONSTRUCTOR_ARG => array($config)
                )
            )
        );
        $this->_framework = $context->getBeanByClassName('point\core\Framework');
    }

    /**
     * Launch Framework
     *
     * @return Framework
     */
    public function launch()
    {
        $this->getFramework()->launcher();
        return $this->getFramework();
    }

    /**
     * @return object|Framework
     */
    public function getFramework()
    {
        return $this->_framework;
    }
}
