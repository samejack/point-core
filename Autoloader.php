<?php

include_once dirname(__FILE__) . '/src/Context.php';

include_once dirname(__FILE__) . '/src/Bootstrap.php';

include_once dirname(__FILE__) . '/src/Framework.php';

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php'))
    include_once dirname(__FILE__) . '/vendor/autoload.php';