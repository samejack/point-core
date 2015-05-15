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
        include_once dirname(__FILE__) . '/ApplicationContext.php';

        $applicationContext = new ApplicationContext();

        date_default_timezone_set('UTC');

        include_once dirname(__FILE__) . '/Framework.php';

        $applicationContext->addConfiguration(
            array(
                array(
                    Bean::CLASS_NAME => '\point\core\Framework',
                )
            )
        );
        $applicationContext->getBeanByClassName('point\core\Framework')->launcher();
    }
}
