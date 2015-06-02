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

        $context = new Context($config);

        include_once dirname(__FILE__) . '/Framework.php';

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
