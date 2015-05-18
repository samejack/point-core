<?php

namespace point\core;

/**
 * Class Bootstrap
 *
 * @author sj
 */
class Bootstrap
{
    public function __construct()
    {
        include_once dirname(__FILE__) . '/Context.php';

        $context = new Context();

        date_default_timezone_set('UTC');

        include_once dirname(__FILE__) . '/Framework.php';

        $context->addConfiguration(
            array(
                array(
                    Bean::CLASS_NAME => '\point\core\Framework',
                )
            )
        );
        $context->getBeanByClassName('point\core\Framework')->launcher();
    }
}
