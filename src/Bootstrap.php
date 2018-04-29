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

        try {
            $this->getFramework()->prepare()->launch()->destroy();
        } catch (\Exception $exception) {
            echo $exception;
            exit(1);
        }
    }

    /**
     * @return \point\core\Framework
     */
    public function getFramework()
    {
        return $this->_framework;
    }
}
