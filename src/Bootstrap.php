<?php

namespace point\core;

/**
 * Class Bootstrap
 *
 * @author sj
 */
class Bootstrap
{
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
        $context->getBeanByClassName('point\core\Framework')->launcher();
    }
}
